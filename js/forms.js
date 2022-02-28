async function getGroups() {
  const response = await fetch(
    `${DIR_SYSTEM}server/request.php?action=getActiveGroups`
  )
  const data = await response.json()
  return data
}

const printGroupDropdown = async (selectedGroupId) => {
  const groups = await getGroups()
  var groupsHtml = ''
  groups.forEach(group => {
    if (selectedGroupId != 'default' && selectedGroupId == group.groupID) {
      groupsHtml += '<option selected="selected" value="' + group.groupID + '">' + group.groupName + '</option>\n';
    } else {
      groupsHtml += '<option value="' + group.groupID + '">' + group.groupName + '</option>\n';
    }
  });
  return `<td>Group:</td>
    <td>
        <div class="select">
            <select id="selectGroupID" name="groupID">
            ${groupsHtml}      
            </select>
        </div>
    </td>`
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

function openTaskForm() {
  printTaskForm()
  toggleDropdown('dropdown_create_content')
}

const printTaskForm = async (selectedGroupId = 'default') => {
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
                '+ await printGroupDropdown(selectedGroupId) + '\
            </tr>\
            </table>\
            <textarea class="input-login" placeholder="title" name="title" cols="40" rows="1"></textarea>\
            <textarea class="input-login" placeholder="description" name="description" cols="40" rows="5"></textarea>\
            <input style="margin-left:25%;" class="submit-login" type="submit" name="createtask-submit" value="Create" />\
            <input type="checkbox" id="createAnother" name="createAnother">\
            <label for="createAnother">Create Another</label>\
            </form>'
  showDynamicForm(document.getElementById("dynamic-modal-content"), html)
  closeDynamicFormListener()
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
    showDynamicForm(container, html);
    document.querySelector('html').style.overflow = 'hidden';
    document.getElementById('bg-modal-dynamicform').style.display = 'flex';
    closeDynamicFormListener();
  }
}

//check nightmode toggled to show dropdown
var nightmodeChangeCheck = document.URL.replace(/.*nightmodechange=([^&]*).*|(.*)/, '$1')
if (nightmodeChangeCheck == 'true') {
  toggleUnfoldArea('dropdown_content', 'dropbtnUnfoldButton')
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
    showDynamicForm(container, html);
    closeDynamicFormListener();
  }
}

// Update Task Form
const openUpdateTaskForm = async () => {
  var taskJsonString = localStorage.getItem('TaskData'), dropDowns = '';
  task = JSON.parse(taskJsonString);
  if (task.taskType == 'task') {
    dropDowns += await printGroupDropdown(task.taskParentID);
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
  showDynamicForm(document.getElementById("dynamic-modal-content"), html);
  closeDynamicFormListener();
}

function addHeaderDynamicForm(title) {
  return '\
  <div class="modal-header">\
      '+ title + '\
      <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>\
  </div>';
}

function showDynamicForm(container, html) {
  container.innerHTML = html;
  document.querySelector('html').style.overflow = 'hidden';
  document.getElementById('bg-modal-dynamicform').style.display = 'flex';
}

function hideDynamicForm() {
  document.getElementById("dynamic-modal-content").innerHTML = ''
  document.getElementById('bg-modal-dynamicform').style.display = 'none';
  document.querySelector('html').style.overflow = 'auto';
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
addCheckboxListener("weatherpanel-checkbox", "toggleWeatherpanel"); //Weather checkbox
addCheckboxListener("timetablepanel-checkbox", "toggleTimetablepanel"); //Timetable checkbox