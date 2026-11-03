/* Rohit's My Tasks page.
   Read this file in order: page helpers, task display, forms, then CSV. */
var tasks = [];
var editingId = '';
var deletingId = '';
var lastTaskButton = null;
var taskDialog = document.getElementById('task-dialog');
var deleteDialog = document.getElementById('delete-dialog');
var taskForm = document.getElementById('task-form');

function getField(id) {
  return document.getElementById(id);
}

function makeTaskId() {
  return 'task-' + Date.now() + '-' + Math.random().toString(36).substring(2, 12);
}

function showMessage(message, isError) {
  getField('notice').textContent = message;
  if (isError) { getField('notice').classList.add('failure'); }
  else { getField('notice').classList.remove('failure'); }
}

// textContent displays user input as text rather than interpreting HTML.
function makeElement(tag, text, className) {
  var element = document.createElement(tag);
  element.textContent = text;
  if (className) { element.className = className; }
  return element;
}

function makeBadge(value) {
  var className = value.toLowerCase().replace(' ', '-');
  return makeElement('span', value, 'badge ' + className);
}

function findTask(id) {
  for (var i = 0; i < tasks.length; i++) {
    if (tasks[i].id === id) { return tasks[i]; }
  }
  return null;
}

function saveTaskList(newList, message) {
  try {
    TrackTask.saveTasks(newList);
    tasks = newList;
    displayTasks();
    showMessage(message, false);
    return true;
  } catch (error) {
    showMessage(error.message, true);
    return false;
  }
}

function refreshCategories() {
  var select = getField('category-filter');
  var selectedCategory = select.value;
  var categories = [];
  for (var i = 0; i < tasks.length; i++) {
    var category = tasks[i].category;
    if (category !== '' && categories.indexOf(category) === -1) { categories.push(category); }
  }
  categories.sort();
  select.textContent = '';
  select.add(new Option('All categories', ''));
  getField('categories').textContent = '';
  for (var j = 0; j < categories.length; j++) {
    select.add(new Option(categories[j], categories[j]));
    getField('categories').appendChild(new Option(categories[j], categories[j]));
  }
  if (categories.indexOf(selectedCategory) !== -1) { select.value = selectedCategory; }
}

function taskMatchesFilters(task) {
  var search = getField('search').value.trim().toLowerCase();
  var words = task.title + ' ' + task.description + ' ' + task.category + ' ' + task.tags.join(' ');
  if (words.toLowerCase().indexOf(search) === -1) { return false; }
  if (getField('category-filter').value !== '' && task.category !== getField('category-filter').value) { return false; }
  if (getField('priority-filter').value !== '' && task.priority !== getField('priority-filter').value) { return false; }
  if (getField('status-filter').value !== '' && task.status !== getField('status-filter').value) { return false; }
  if (getField('date-filter').value !== '' && task.dueDate !== getField('date-filter').value) { return false; }
  return true;
}

function makeActionButton(label, task, action) {
  var button = makeElement('button', label, '');
  button.type = 'button';
  button.setAttribute('aria-label', label + ': ' + task.title);
  if (action === 'edit') { button.onclick = function () { openTaskForm(task, button); }; }
  if (action === 'delete') {
    button.className = 'delete-action';
    button.onclick = function () { openDeleteForm(task, button); };
  }
  if (action === 'complete') { button.onclick = function () { changeTaskStatus(task.id); }; }
  return button;
}

function addTaskRow(task) {
  var row = document.createElement('tr');
  var nameCell = document.createElement('td');
  nameCell.appendChild(makeElement('span', task.title, 'task-title'));
  var details = task.category || 'Uncategorized';
  for (var i = 0; i < task.tags.length; i++) { details += ' / #' + task.tags[i]; }
  nameCell.appendChild(makeElement('span', details, 'task-meta'));
  var date = new Date(task.dueDate + 'T12:00:00');
  var dateCell = makeElement('td', date.toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'}), '');
  if (task.status !== 'Completed' && task.dueDate < TrackTask.today()) {
    dateCell.appendChild(makeElement('span', 'Overdue', 'overdue'));
  }
  var priorityCell = document.createElement('td');
  priorityCell.appendChild(makeBadge(task.priority));
  var statusCell = document.createElement('td');
  statusCell.appendChild(makeBadge(task.status));
  var actionsCell = document.createElement('td');
  var actions = makeElement('div', '', 'actions');
  actions.appendChild(makeActionButton('Edit', task, 'edit'));
  actions.appendChild(makeActionButton('Delete', task, 'delete'));
  var completeLabel = 'Complete';
  if (task.status === 'Completed') { completeLabel = 'Reopen'; }
  actions.appendChild(makeActionButton(completeLabel, task, 'complete'));
  actionsCell.appendChild(actions);
  row.appendChild(nameCell);
  row.appendChild(dateCell);
  row.appendChild(priorityCell);
  row.appendChild(statusCell);
  row.appendChild(actionsCell);
  getField('task-rows').appendChild(row);
}

function displayTasks() {
  refreshCategories();
  getField('task-rows').textContent = '';
  var shown = 0;
  for (var i = 0; i < tasks.length; i++) {
    if (taskMatchesFilters(tasks[i])) { addTaskRow(tasks[i]); shown++; }
  }
  getField('empty-state').hidden = shown !== 0;
  getField('task-count').textContent = '(' + shown + ')';
  var summary = TrackTask.getSummary(tasks);
  getField('summary').textContent = 'Showing ' + shown + ' of ' + tasks.length + ' tasks · ' + summary.completed + ' completed';
}

function clearFormErrors() {
  getField('form-error').textContent = '';
  for (var i = 0; i < taskForm.elements.length; i++) {
    taskForm.elements[i].removeAttribute('aria-invalid');
    taskForm.elements[i].removeAttribute('aria-errormessage');
  }
}

function openTaskForm(task, button) {
  lastTaskButton = button;
  editingId = '';
  taskForm.reset();
  clearFormErrors();
  getField('dialog-title').textContent = 'Add Task';
  getField('due-date').value = TrackTask.today();
  if (task) {
    editingId = task.id;
    getField('dialog-title').textContent = 'Edit Task';
    getField('title').value = task.title;
    getField('description').value = task.description;
    getField('due-date').value = task.dueDate;
    getField('priority').value = task.priority;
    getField('status').value = task.status;
    getField('category').value = task.category;
    getField('tags').value = task.tags.join(', ');
  }
  taskDialog.showModal();
  getField('title').focus();
}

function formError(field, message) {
  getField('form-error').textContent = message;
  field.setAttribute('aria-invalid', 'true');
  field.setAttribute('aria-errormessage', 'form-error');
  field.focus();
}

function readTags(text) {
  var pieces = text.split(',');
  var tags = [];
  for (var i = 0; i < pieces.length; i++) {
    var tag = pieces[i].trim();
    if (tag !== '' && tags.indexOf(tag) === -1) { tags.push(tag); }
  }
  return tags;
}

function submitTask(event) {
  event.preventDefault();
  clearFormErrors();
  if (getField('title').value.trim() === '') {
    formError(getField('title'), 'Enter a task title. Spaces alone are not accepted.');
    return;
  }
  if (!TrackTask.validDate(getField('due-date').value)) {
    formError(getField('due-date'), 'Choose a valid due date.');
    return;
  }
  // Check HTML required, maxlength and other browser validation rules.
  for (var i = 0; i < taskForm.elements.length; i++) {
    var field = taskForm.elements[i];
    if (field.willValidate && !field.checkValidity()) { formError(field, field.validationMessage); return; }
  }
  var tags = readTags(getField('tags').value);
  if (tags.length > 10) { formError(getField('tags'), 'Use up to 10 tags.'); return; }
  for (var j = 0; j < tags.length; j++) {
    if (tags[j].length > 30) { formError(getField('tags'), 'Each tag must be 30 characters or fewer.'); return; }
  }
  var id = editingId;
  if (id === '') { id = makeTaskId(); }
  var task = {
    id: id,
    title: getField('title').value.trim(),
    description: getField('description').value.trim(),
    dueDate: getField('due-date').value,
    priority: getField('priority').value,
    status: getField('status').value,
    category: getField('category').value.trim(),
    tags: tags
  };
  var message = taskValidationMessage(task);
  if (message !== '') { formError(getField('title'), message); return; }
  var newList = tasks.slice();
  if (editingId === '') { newList.push(task); }
  else {
    for (var k = 0; k < newList.length; k++) {
      if (newList[k].id === editingId) { newList[k] = task; break; }
    }
  }
  var successMessage = 'Task added successfully.';
  if (editingId !== '') { successMessage = 'Task updated successfully.'; }
  if (saveTaskList(newList, successMessage)) { taskDialog.close(); }
}

function changeTaskStatus(id) {
  // Copy records so a failed save does not change the visible task array.
  var newList = JSON.parse(JSON.stringify(tasks));
  var message = '';
  for (var i = 0; i < newList.length; i++) {
    if (newList[i].id === id) {
      if (newList[i].status === 'Completed') { newList[i].status = 'To-Do'; message = 'Task reopened.'; }
      else { newList[i].status = 'Completed'; message = 'Task marked as completed.'; }
      break;
    }
  }
  if (saveTaskList(newList, message)) {
    var buttons = getField('task-rows').getElementsByTagName('button');
    var task = findTask(id);
    for (var j = 0; j < buttons.length; j++) {
      var label = buttons[j].getAttribute('aria-label');
      if (label === 'Reopen: ' + task.title || label === 'Complete: ' + task.title) { buttons[j].focus(); return; }
    }
    getField('add-task').focus();
  }
}

function openDeleteForm(task, button) {
  deletingId = task.id;
  lastTaskButton = button;
  getField('delete-message').textContent = 'Delete “' + task.title + '” from this browser? This cannot be undone.';
  deleteDialog.showModal();
  getField('cancel-delete').focus();
}

function deleteTask() {
  var newList = [];
  for (var i = 0; i < tasks.length; i++) {
    if (tasks[i].id !== deletingId) { newList.push(tasks[i]); }
  }
  if (saveTaskList(newList, 'Task deleted.')) { deleteDialog.close(); }
}

function restoreTaskFocus() {
  if (lastTaskButton && lastTaskButton.isConnected) { lastTaskButton.focus(); }
  else { getField('add-task').focus(); }
}

// CSV files need quoted cells because descriptions can contain commas or new lines.
var csvColumns = ['title', 'description', 'dueDate', 'priority', 'status', 'category', 'tags'];
function quoteCSV(value) {
  var text = String(value);
  // Stop spreadsheet programs treating user text as a formula.
  if (/^\s*[=+@-]/.test(text)) { text = "'" + text; }
  return '"' + text.split('"').join('""') + '"';
}

function exportTasks() {
  var lines = [csvColumns.join(',')];
  for (var i = 0; i < tasks.length; i++) {
    var cells = [];
    for (var j = 0; j < csvColumns.length; j++) {
      var value = tasks[i][csvColumns[j]];
      if (csvColumns[j] === 'tags') { value = tasks[i].tags.join('|'); }
      cells.push(quoteCSV(value));
    }
    lines.push(cells.join(','));
  }
  var file = new Blob(['\uFEFF' + lines.join('\r\n')], {type: 'text/csv;charset=utf-8;'});
  var url = URL.createObjectURL(file);
  var link = document.createElement('a');
  link.href = url;
  link.download = 'track-task-export.csv';
  link.click();
  setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
  showMessage('All tasks exported as CSV.', false);
}

/* CSV is the most detailed helper here. It reads one character at a time.
   A comma ends a cell only outside quotes. Two quotes inside a quoted cell
   represent one literal quote. A new line ends a row only outside quotes. */
function parseCSV(text) {
  text = text.replace(/^\uFEFF/, '');
  var rows = [];
  var row = [];
  var cell = '';
  var insideQuotes = false;
  for (var i = 0; i < text.length; i++) {
    var character = text[i];
    if (character === '"') {
      if (insideQuotes && text[i + 1] === '"') { cell += '"'; i++; }
      else { insideQuotes = !insideQuotes; }
    } else if (character === ',' && !insideQuotes) {
      row.push(cell); cell = '';
    } else if ((character === '\n' || character === '\r') && !insideQuotes) {
      if (character === '\r' && text[i + 1] === '\n') { i++; }
      row.push(cell);
      if (row.join('') !== '') { rows.push(row); }
      row = []; cell = '';
    } else { cell += character; }
  }
  if (insideQuotes) { throw new Error('CSV contains an unclosed quoted field.'); }
  row.push(cell);
  if (row.join('') !== '') { rows.push(row); }
  return rows;
}

function importCSVText(text) {
  var rows = parseCSV(text);
  if (rows.length === 0 || rows[0].join(',') !== csvColumns.join(',')) {
    throw new Error('CSV header must be: ' + csvColumns.join(','));
  }
  if (rows.length < 2 || rows.length > 501) { throw new Error('Import between 1 and 500 tasks at a time.'); }
  var imported = [];
  for (var i = 1; i < rows.length; i++) {
    if (rows[i].length !== 7) { throw new Error('CSV row ' + (i + 1) + ' must contain 7 columns.'); }
    var tags = [];
    if (rows[i][6].trim() !== '') {
      var pieces = rows[i][6].split('|');
      for (var j = 0; j < pieces.length; j++) {
        if (pieces[j].trim() !== '') { tags.push(pieces[j].trim()); }
      }
    }
    var task = {
      id: makeTaskId(), title: rows[i][0].trim(), description: rows[i][1].trim(),
      dueDate: rows[i][2].trim(), priority: rows[i][3].trim(), status: rows[i][4].trim(),
      category: rows[i][5].trim(), tags: tags
    };
    var error = taskValidationMessage(task);
    if (error !== '') { throw new Error('CSV row ' + (i + 1) + ': ' + error); }
    imported.push(task);
  }
  // Validate the whole file before adding any records.
  saveTaskList(tasks.concat(imported), imported.length + ' tasks imported. Existing tasks were kept.');
}

function chooseCSV(event) {
  var file = event.target.files[0];
  if (!file) { return; }
  if (!file.name.toLowerCase().endsWith('.csv') || file.size > 1000000) {
    showMessage('Choose a CSV file smaller than 1 MB.', true);
    event.target.value = '';
    return;
  }
  // FileReader reads the user's selected local file without sending it anywhere.
  var reader = new FileReader();
  reader.onload = function () {
    try { importCSVText(reader.result); }
    catch (error) { showMessage(error.message, true); }
    getField('csv-file').value = '';
  };
  reader.onerror = function () {
    showMessage('The CSV file could not be read.', true);
    getField('csv-file').value = '';
  };
  reader.readAsText(file);
}

// Connect the page controls to the functions above.
getField('add-task').onclick = function () { openTaskForm(null, getField('add-task')); };
getField('close-dialog').onclick = function () { taskDialog.close(); };
getField('cancel-dialog').onclick = function () { taskDialog.close(); };
taskDialog.addEventListener('close', restoreTaskFocus);
taskForm.onsubmit = submitTask;
getField('cancel-delete').onclick = function () { deleteDialog.close(); };
getField('confirm-delete').onclick = deleteTask;
deleteDialog.addEventListener('close', restoreTaskFocus);
getField('filters').onsubmit = function (event) { event.preventDefault(); };
getField('filters').addEventListener('input', displayTasks);
getField('clear-filters').onclick = function () {
  getField('filters').reset(); displayTasks(); getField('search').focus();
};
getField('export-tasks').onclick = exportTasks;
getField('import-tasks').onclick = function () { getField('csv-file').click(); };
getField('csv-file').onchange = chooseCSV;
var plannedLinks = document.querySelectorAll('a[href="#planned-features"]');
for (var linkIndex = 0; linkIndex < plannedLinks.length; linkIndex++) {
  plannedLinks[linkIndex].onclick = function () { getField('planned-features').open = true; };
}

// Refresh this page when another tab saves tasks on the same website.
window.addEventListener('storage', function (event) {
  if (event.key === TrackTask.KEY) {
    try { tasks = TrackTask.getTasks(); displayTasks(); showMessage('Tasks updated from another tab.', false); }
    catch (error) { showMessage(error.message, true); }
  }
});

// Load saved tasks when the page opens.
try {
  tasks = TrackTask.getTasks();
  displayTasks();
} catch (error) {
  showMessage(error.message, true);
  getField('add-task').disabled = true;
  getField('import-tasks').disabled = true;
  getField('export-tasks').disabled = true;
}
// Sheidu's Create Task link opens the form directly.
var pageOptions = new URLSearchParams(location.search);
if (pageOptions.get('add') === '1' && !getField('add-task').disabled) {
  openTaskForm(null, getField('add-task'));
}
