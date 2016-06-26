<!DOCTYPE html>
<html>
<head>
<!-- Load the jQuery UI styles. -->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">

<!-- Custom styles. -->
<style>
  body {
    width: 100%;
    font-family: Arial, sans-serif;
    font-size: 13px;
    margin: 0px;
    padding: 0px;
  }
  #tasks-panel {
    margin-top: 10px;
  }
  #tasks {
    padding: 0;
    list-style-type: none;
  }
  #tasklist {
     margin-bottom:5px;
  }
  #task-title {
    width: 450px;
  }
  #task-note {
    width: calc(100% - 20px);
    height: 150px;
    margin-top:5px;
  }
  #outer {
    width: 100%;
    padding: 10px;
    position: relative;
    box-sizing: border-box; 
  }
</style>
</head>
<body>
<div id="outer">
<label for="tasklist">Select a task list: </label>
<select id="tasklist">
  <option>Loading...</option>
</select>
<br/>
    <label for="task-title">Title:</label>
    <input type="text" name="task-title" id="task-title" autofocus value="<?= startingTitle ?>"/>
    <label for="task-date">Date:</label>
    <input type="text" name="task-date" id="task-date" />
    <textarea name="task-note" id="task-note"><?= startingNote ?></textarea>
    <form name="new-task" id="new-task"> 
    <input type="submit" name="add" id="add-button" value="Add" />
  </form>
</div>

<!-- Load the jQuery and jQuery UI libraries. -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

<!-- Custom client-side JavaScript code. -->
<script>
  // When the page loads.
  $(function() {
    $('#tasklist').bind('change', loadTasks);
    $('#new-task').bind('submit', onNewTaskFormSubmit);
    
    datePicker = $("#task-date").datepicker({
      firstDay: 0
    });
    
    loadTaskLists();
  });

  /**
   * Load the available task lists.
   */
  function loadTaskLists() {
    google.script.run.withSuccessHandler(showTaskLists)
        .withFailureHandler(showError)
        .getTaskLists();
  }

  /**
   * Show the returned task lists in the dropdown box.
   * @param {Array.<Object>} taskLists The task lists to show.
   */
  function showTaskLists(taskLists) {
    var select = $('#tasklist');
    select.empty();
    taskLists.forEach(function(taskList) {
      var option = $('<option>')
          .attr('value', taskList.id)
          .text(taskList.name);
      select.append(option);
    });
  }

  /**
   * Load the tasks in the currently selected task list.
   */
  function loadTasks() {
  }

  /**
   * Show the returned tasks on the page.
   * @param {Array.<Object>} tasks The tasks to show.
   */
  function showTasks(tasks) {
    var list = $('#tasks').empty();
    if (tasks.length > 0) {
      tasks.forEach(function(task) {
        var item = $('<li>');
        var checkbox = $('<input type="checkbox">')
            .data('taskId', task.id)
            .bind('change', onCheckBoxChange);
        item.append(checkbox);

        var title = $('<span>')
            .text(task.title);
        item.append(title);

        if (task.completed) {
          checkbox.prop('checked', true);
          title.css('text-decoration', 'line-through')
        }

        list.append(item);
      });
    } else {
      list.text('No tasks');
    }
  }

  /**
   * A callback function that runs when a task is checked or unchecked.
   */
  function onCheckBoxChange() {
    var checkbox = $(this);
    var title = checkbox.siblings('span');
    var isChecked = checkbox.prop('checked');
    var taskListId = $('#tasklist').val();
    var taskId = checkbox.data('taskId');
    if (isChecked) {
      title.css('text-decoration', 'line-through');
    } else {
      title.css('text-decoration', 'none');
    }
    google.script.run.withSuccessHandler(function() {
      title.effect("highlight", {
        duration: 1500
      });
    }).withFailureHandler(showError)
      .setCompleted(taskListId, taskId, isChecked);
  }

  /**
   * A callback function that runs when the new task form is submitted.
   */
  function onNewTaskFormSubmit() {
    var taskListId = $('#tasklist').val();
    var titleTextBox = $('#task-title');
    var noteTextBox = $('#task-note');
    var title = titleTextBox.val();
    var note = noteTextBox.val();
    var dateObj = $("#task-date").datepicker( "getDate" );
    var date = dateObj ? dateObj.toISOString() : undefined;
    google.script.run.withSuccessHandler(function() {
       $("#outer").html("<p>Task added.</p>");
    }).withFailureHandler(showError)
      .addTask(taskListId, title, note, date);
    return false;
  }

  /**
   * Logs an error message and shows an alert to the user.
   */
  function showError(error) {
    console.log(error);
    window.alert('An error has occurred, please try again.');
  }
</script>

</body>
</html>
