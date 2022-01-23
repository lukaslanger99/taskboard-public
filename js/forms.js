function printGroupDropdown(selectedGroupId) {
  var groups = JSON.parse(localStorage.getItem('Groups')), groupsHtml = '';
  for (var group in groups) {
    if (selectedGroupId != 'default' && selectedGroupId == group) {
      groupsHtml += '<option selected="selected" value="' + group + '">' + groups[group] + '</option>\n';
    } else {
      groupsHtml += '<option value="' + group + '">' + groups[group] + '</option>\n';
    }
  }

  var html = '\
  <td>Group:</td>\
  <td>\
      <div class="select">\
          <select name="groupID">\
          '+ groupsHtml + '\
          </select>\
      </div>\
  </td>';
  return html;
}

function printPriorityDropdown(selectedPriority = '2') {
  var priorityHtml = '';
  if (selectedPriority == 1) {
    priorityHtml += '\
    <option selected="selected" value="1">Low</option>\
    <option value="2">Normal</option>\
    <option value="3">High</option>\
    ';
  } else if (selectedPriority == 2) {
    priorityHtml += '\
    <option value="1">Low</option>\
    <option selected="selected" value="2">Normal</option>\
    <option value="3">High</option>\
    ';
  } else {
    priorityHtml += '\
    <option selected="selected" value="1">Low</option>\
    <option value="2">Normal</option>\
    <option selected="selected" value="3">High</option>\
    ';
  }

  var html = '\
  <td>Priority:</td>\
  <td>\
      <div class="select">\
          <select name="priority">\
          '+ priorityHtml + '\
          </select>\
      </div>\
  </td>';
  return html;
}

function printTaskForm(selectedGroupId = 'default') {
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    toggleDropdown('dropdown_create_content');
    var html = '\
    '+ addHeaderDynamicForm('Create Task') + '\
    <form action="'+ DIR_SYSTEM + 'php/action.php?action=createTask" autocomplete="off" method="post" >\
        <table style="margin:0 auto 15px auto;">\
            <tr>\
                <td>Priority:</td>\
                <td>\
                    <div class="select">\
                        <select name="priority">\
                            <option value="1">Low</option>\
                            <option selected="selected" value="2">Normal</option>\
                            <option value="3">High</option>\
                        </select>\
                    </div>\
                </td>\
                '+ printGroupDropdown(selectedGroupId) + '\
            </tr>\
        </table>\
        <textarea class="input-login" placeholder="title" name="title" cols="40" rows="1"></textarea>\
        <textarea class="input-login" placeholder="description" name="description" cols="40" rows="5"></textarea>\
        <input style="margin-left:25%;" class="submit-login" type="submit" name="createtask-submit" value="Create" />\
        <input type="checkbox" id="createAnother" name="createAnother">\
        <label for="createAnother">Create Another</label>\
    </form>';
    showDynamicForm(container, html, 280);
    closeDynamicFormListener();
  }
}

function printSubtaskForm() {
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    var taskId = document.URL.replace(/.*id=([^&]*).*|(.*)/, '$1');
    var html = '\
    '+ addHeaderDynamicForm('Create Subtask') + '\
    <form action="'+ DIR_SYSTEM + 'php/action.php?action=createSubtask&taskId=' + taskId + '" autocomplete="off" method="post" >\
        <table style="margin:0 auto 15px auto;">\
            <tr>\
                <td>Priority:</td>\
                <td>\
                    <div class="select">\
                        <select name="priority">\
                            <option value="1">Low</option>\
                            <option selected="selected" value="2">Normal</option>\
                            <option value="3">High</option>\
                        </select>\
                    </div>\
                </td>\
            </tr>\
        </table>\
        <textarea class="input-login" placeholder="title" name="title" cols="40" rows="1"></textarea>\
        <textarea class="input-login" placeholder="description" name="description" cols="40" rows="5"></textarea>\
        <input style="margin-left:25%;" class="submit-login" type="submit" name="createtask-submit" value="Create" />\
        <input type="checkbox" id="createAnother" name="createAnother">\
        <label for="createAnother">Create Another</label>\
    </form>';
    container.innerHTML = html;
    showDynamicForm(container, html, 280);
    document.querySelector('html').style.overflow = 'hidden';
    document.getElementById('bg-modal-dynamicform').style.display = 'flex';
    closeDynamicFormListener();
  }
}

//check create another task
var createAnotherTaskCheck = document.URL.replace(/.*createTask=([^&]*).*|(.*)/, '$1');
if (createAnotherTaskCheck == 'true') {
  printTaskForm(document.URL.replace(/.*groupID=([^&]*).*|(.*)/, '$1'));
}

//check create another subtask
var createAnotherTaskCheck = document.URL.replace(/.*createSubtask=([^&]*).*|(.*)/, '$1');
if (createAnotherTaskCheck == 'true') {
  printSubtaskForm();
}

// Subtask Form
var createSubtaskButton = document.getElementById('createSubtaskButton');
if (createSubtaskButton) {
  createSubtaskButton.addEventListener('click',
    function () {
      printSubtaskForm();
    }
  )
}

// Group Form
function printGroupForm() {
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    toggleDropdown('dropdown_create_content');
    var html = '\
      '+ addHeaderDynamicForm('Create Group') + '\
      <form action="'+ DIR_SYSTEM + 'php/action.php?action=createGroup" autocomplete="off" method="post" >\
          <input class="input-login" placeholder="name" type="text" name="name"/>\
          <input class="submit-login" type="submit" name="creategroup-submit" value="Create" />\
      </form>';
    showDynamicForm(container, html, 200);
    closeDynamicFormListener();
  }
}

// RT Form
var createRTButton = document.getElementById('createRTButton');
if (createRTButton) {
  createRTButton.addEventListener('click',
    function () {
      var container = document.getElementById("dynamic-modal-content");
      if (container) {
        var html = '\
        '+ addHeaderDynamicForm('Create Repeating Task') + '\
        <form action="'+ DIR_SYSTEM + 'php/action.php?action=createRepeatingtask" autocomplete="off" method="post" >\
            <table style="margin:0 auto 15px auto;">    \
                <tr>\
                    <td>\
                        <input type="checkbox"  id="taskEverySecondDay" name="taskEverySecondDay">\
                        <label for="taskEverySecondDay">Every Second Day</label>\
                    </td>\
                    <td>\
                        <input type="checkbox"  id="taskStartToday" name="taskStartToday">\
                        <label for="taskStartToday">Start Today</label>\
                    </td>\
                </tr>\
            </table>\
            <table id="rtWeekdayAndQuantity" style="margin:0 auto 15px auto;">\
                <tr>\
                    <td>Weekday:</td>\
                    <td>\
                        <div class="select">\
                            <select class="weekday" name="weekday">\
                                <option selected="selected" value="everyday">Everyday</option>\
                                <option value="Mon">Monday</option>\
                                <option value="Tue">Tuesday</option>\
                                <option value="Wed">Wednesday</option>\
                                <option value="Thu">Thursday</option>\
                                <option value="Fri">Friday</option>\
                                <option value="Sat">Saturday</option>\
                                <option value="Sun">Sunday</option>\
                            </select>\
                        </div>\
                    </td>\
                    <td>Quantity:</td>\
                    <td>\
                        <div class="select">\
                            <select class="quantity" name="quantity">\
                                <option selected="selected" value="everyweek">Everyweek</option>\
                                <option value="odd">Odd</option>\
                                <option value="even">Even</option>\
                            </select>\
                        </div>\
                    </td>\
                </tr>\
            </table>\
            <textarea class="input-login" placeholder="name" name="title" rows="1"></textarea>\
            <input class="submit-login" type="submit" name="creatert-submit" value="Create" />\
        </form>';
        showDynamicForm(container, html, 250);
        closeDynamicFormListener();
      }
    }
  )
}

var checkboxTaskEverySecondDay = document.getElementById('taskEverySecondDay');
if (checkboxTaskEverySecondDay) {
  checkboxTaskEverySecondDay.addEventListener('change', (event) => {
    if (event.target.checked) {
      document.getElementById('rtWeekdayAndQuantity').style.display = 'none';
    } else {
      document.getElementById('rtWeekdayAndQuantity').style.display = '';
    }
  });
}

// MOTD Form
var createMOTDButton = document.getElementById('createMOTDButton');
if (createMOTDButton) {
  createMOTDButton.addEventListener('click',
    function () {
      var container = document.getElementById("dynamic-modal-content");
      if (container) {
        var html = '\
        '+ addHeaderDynamicForm('Create Message of the Day') + '\
        <form action="'+ DIR_SYSTEM + 'php/action.php?action=createMotd" autocomplete="off" method="post" >\
            <table style="margin:0 auto 15px auto;">\
                <tr>\
                '+ printGroupDropdown() + '\
                </tr>\
            </table>\
            <textarea class="input-login" placeholder="name" name="title" rows="1"></textarea>\
            <input class="submit-login" type="submit" name="createmotd-submit" value="Create" />\
        </form>';
        showDynamicForm(container, html, 210);
        closeDynamicFormListener();
      }
    }
  )
}

// Appointment Form
var createMOTDButton = document.getElementById('createAppointmentButton');
if (createMOTDButton) {
  createMOTDButton.addEventListener('click',
    function () {
      var container = document.getElementById("dynamic-modal-content");
      if (container) {
        var html = '\
        '+ addHeaderDynamicForm('Create Appointment') + '\
        <form action="'+ DIR_SYSTEM + 'php/action.php?action=createAppointment" autocomplete="off" method="post" >\
            <table style="margin:0 auto 15px auto;">\
                <tr>\
                    '+ printGroupDropdown() + '\
                    <td>Date:</td>\
                    <td>\
                        <input type="date" name="date">\
                    </td>\
                </tr>\
            </table>\
            <textarea class="input-login" placeholder="name" name="title" rows="1"></textarea>\
            <input class="submit-login" type="submit" name="createappointment-submit" value="Create" />\
        </form>';
        showDynamicForm(container, html, 230);
        closeDynamicFormListener();
      }
    }
  )
}

// Update Task Form
var createTaskButton = document.getElementById('updatetask-button');
if (createTaskButton) {
  createTaskButton.addEventListener('click',
    function () {
      var container = document.getElementById("dynamic-modal-content");
      if (container) {
        var taskJsonString = localStorage.getItem('TaskData'), dropDowns = '';
        task = JSON.parse(taskJsonString);
        if (task.taskType == 'task') {
          dropDowns += printGroupDropdown(task.taskParentID);
        }
        dropDowns += printPriorityDropdown(task.taskPriority);

        var html = '\
        '+ addHeaderDynamicForm('Update Task') + '\
        <form action="'+ DIR_SYSTEM + 'php/action.php?action=update&id=' + task.taskID + '" autocomplete="off" method="post" >\
            <table style="margin:0 auto 15px auto;">\
                <tr>\
                    '+ dropDowns + '\
                </tr>\
            </table>\
            <textarea class="input-login" type="text" name="title" cols="40" rows="1">'+ task.taskTitle + '</textarea>\
            <textarea class="input-login" type="text" name="description" cols="40" rows="5">'+ task.taskDescription + '</textarea>\
            <input class="submit-login" type="submit" name="updatetask-submit" value="Update" />\
        </form>';
        showDynamicForm(container, html, 280);
        closeDynamicFormListener();
      }
    }
  )
}

function addHeaderDynamicForm(title) {
  return '\
  <div class="modal-header">\
      '+ title + '\
      <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>\
  </div>';
}

function showDynamicForm(container, html, boxHeight) {
  container.innerHTML = html;
  container.style.height = boxHeight + 'px';
  document.querySelector('html').style.overflow = 'hidden';
  document.getElementById('bg-modal-dynamicform').style.display = 'flex';
}

function closeDynamicFormListener() {
  var faCloseDynamicform = document.getElementById('fa-close-dynamicform');
  if (faCloseDynamicform) {
    faCloseDynamicform.addEventListener('click',
      function () {
        var container = document.getElementById("dynamic-modal-content");
        if (container) {
          container.innerHTML = '';
          document.getElementById('bg-modal-dynamicform').style.display = 'none';
          document.querySelector('html').style.overflow = 'auto';
        }
      }
    )
  }
}

function addCheckboxListener(elementId, phpAction) {
  var checkboxElement = document.getElementById(elementId);
  if (checkboxElement) {
    checkboxElement.addEventListener("click",
      function () {
        location.href = DIR_SYSTEM + "php/profile.inc.php?action=" + phpAction + "&n=" + checkboxElement.checked;
      }
    )
  }
}

addCheckboxListener("nightmode-checkbox", "toggleNightmode"); //Nightmode checkbox
addCheckboxListener("rtpanel-checkbox", "toggleRTpanel"); //RT checkbox
addCheckboxListener("motdpanel-checkbox", "toggleMOTDpanel"); //MOTD checkbox
addCheckboxListener("appointmentpanel-checkbox", "toggleAppointmentpanel"); //Appointment checkbox
addCheckboxListener("queuepanel-checkbox", "toggleQueuepanel"); //Queue checkbox