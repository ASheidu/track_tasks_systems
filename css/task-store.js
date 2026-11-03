/* Shared task data for Rohit's task page and Sheidu's dashboard.
   localStorage saves text in this browser. JSON converts arrays to/from text. */
var TrackTask = {};
TrackTask.KEY = 'trackTask.tasks.v1';

// Turn a Date into YYYY-MM-DD, using the user's local date.
function formatTaskDate(date) {
  var month = date.getMonth() + 1;
  var day = date.getDate();
  if (month < 10) { month = '0' + month; }
  if (day < 10) { day = '0' + day; }
  return date.getFullYear() + '-' + month + '-' + day;
}

TrackTask.today = function () {
  return formatTaskDate(new Date());
};

function sampleDueDate(daysFromToday) {
  var date = new Date();
  date.setDate(date.getDate() + daysFromToday);
  return formatTaskDate(date);
}

TrackTask.validDate = function (value) {
  if (typeof value !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return false;
  }
  var date = new Date(value + 'T12:00:00');
  return !isNaN(date.getTime()) && date.getFullYear() > 0 && formatTaskDate(date) === value;
};

// Return an error message when a record contains invalid values.
function taskValidationMessage(task) {
  if (!task || typeof task !== 'object') { return 'Invalid task record.'; }
  if (typeof task.title !== 'string' || task.title.trim().length === 0 || task.title.length > 100) {
    return 'Enter a task title with 1 to 100 characters.';
  }
  if (typeof task.description !== 'string' || task.description.length > 1000) {
    return 'Descriptions must be 1,000 characters or fewer.';
  }
  if (!TrackTask.validDate(task.dueDate)) { return 'Choose a valid due date.'; }
  if (task.priority !== 'High' && task.priority !== 'Medium' && task.priority !== 'Low') {
    return 'Choose High, Medium, or Low priority.';
  }
  if (task.status !== 'To-Do' && task.status !== 'In Progress' && task.status !== 'Completed') {
    return 'Choose a listed task status.';
  }
  if (typeof task.category !== 'string' || task.category.length > 50) {
    return 'Categories must be 50 characters or fewer.';
  }
  if (!Array.isArray(task.tags) || task.tags.length > 10) { return 'Use up to 10 tags.'; }
  for (var i = 0; i < task.tags.length; i++) {
    if (typeof task.tags[i] !== 'string' || task.tags[i].length > 30) {
      return 'Each tag must be 30 characters or fewer.';
    }
  }
  return '';
}

TrackTask.validate = function (task) {
  var message = taskValidationMessage(task);
  if (message !== '') { throw new Error(message); }
  return task;
};

function createSampleTasks() {
  return [
    {id: 'sample-1', title: 'Finish project proposal', description: 'Review the final group proposal.', dueDate: sampleDueDate(-1), priority: 'High', status: 'To-Do', category: 'School', tags: ['group']},
    {id: 'sample-2', title: 'Review task page layout', description: 'Check spacing and keyboard navigation.', dueDate: sampleDueDate(0), priority: 'Medium', status: 'In Progress', category: 'Web Project', tags: ['design']},
    {id: 'sample-3', title: 'Organize chapter notes', description: '', dueDate: sampleDueDate(2), priority: 'Low', status: 'Completed', category: 'Personal', tags: ['reading']}
  ];
}

TrackTask.getTasks = function () {
  // try/catch displays a useful message if storage is blocked or data is damaged.
  var saved;
  try {
    saved = localStorage.getItem(TrackTask.KEY);
  } catch (error) {
    throw new Error('Browser storage is unavailable. Allow site storage to use tasks.');
  }
  if (saved === null) {
    var sampleTasks = createSampleTasks();
    TrackTask.saveTasks(sampleTasks);
    return sampleTasks;
  }
  try {
    var savedTasks = JSON.parse(saved);
    if (!Array.isArray(savedTasks)) { throw new Error('Invalid saved list.'); }
    var usedIds = [];
    for (var i = 0; i < savedTasks.length; i++) {
      TrackTask.validate(savedTasks[i]);
      var id = savedTasks[i].id;
      if (typeof id !== 'string' || id === '' || usedIds.indexOf(id) !== -1) {
        throw new Error('Invalid or duplicate task ID.');
      }
      usedIds.push(id);
    }
    return savedTasks;
  } catch (error) {
    throw new Error('Saved tasks cannot be read. Existing data was not overwritten.');
  }
};

TrackTask.saveTasks = function (taskList) {
  for (var i = 0; i < taskList.length; i++) { TrackTask.validate(taskList[i]); }
  try {
    localStorage.setItem(TrackTask.KEY, JSON.stringify(taskList));
  } catch (error) {
    throw new Error('Tasks could not be saved. Browser storage may be blocked or full.');
  }
};

// A regular loop counts the tasks for Sheidu's dashboard.
TrackTask.getSummary = function (taskList) {
  if (taskList === undefined) { taskList = TrackTask.getTasks(); }
  var result = {total: taskList.length, completed: 0, inProgress: 0, overdue: 0, upcoming: 0, progress: 0};
  var currentDate = TrackTask.today();
  for (var i = 0; i < taskList.length; i++) {
    var task = taskList[i];
    if (task.status === 'Completed') { result.completed++; }
    if (task.status === 'In Progress') { result.inProgress++; }
    if (task.status !== 'Completed') {
      if (task.dueDate < currentDate) { result.overdue++; }
      else { result.upcoming++; }
    }
  }
  if (result.total > 0) { result.progress = Math.round(result.completed / result.total * 100); }
  return result;
};
