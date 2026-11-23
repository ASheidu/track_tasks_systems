// PHP loads all task data and processes forms. This only enhances the dialog UI.
var taskEditor = document.querySelector('.task-editor');

if (taskEditor && typeof taskEditor.showModal === 'function') {
    // The open attribute provides an in-page form when JavaScript is unavailable.
    taskEditor.removeAttribute('open');
    taskEditor.showModal();

    var invalidField = taskEditor.querySelector('[aria-invalid="true"]');
    var titleField = taskEditor.querySelector('[name="title"]');
    if (invalidField) {
        invalidField.focus();
    } else if (titleField) {
        titleField.focus();
    } else {
        taskEditor.querySelector('a[href="tasks.php"]').focus();
    }

    taskEditor.addEventListener('cancel', function (event) {
        event.preventDefault();
        window.location.assign('tasks.php');
    });
}
