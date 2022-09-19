const printGroupDropdown = async (selectedGroupId) => {
  const response = await requestHandler.sendRequest('getActiveGroups')
  const groups = response.data
  if (!groups) return printErrorToast('NO_GROUPS')
  var groupsHtml = ''
  groups.forEach(group => {
    if (selectedGroupId && selectedGroupId == group.groupID) {
      groupsHtml += '<option selected="selected" value="' + group.groupID + '">' + group.groupName + '</option>\n';
    } else {
      groupsHtml += '<option value="' + group.groupID + '">' + group.groupName + '</option>\n';
    }
  });
  return `<p>Group:</p>
    <p>
        <div class="select">
            <select id="selectGroupID" name="groupID">
            ${groupsHtml}      
            </select>
        </div>
    </p>`
}


//check nightmode toggled to show dropdown
var nightmodeChangeCheck = document.URL.replace(/.*nightmodechange=([^&]*).*|(.*)/, '$1')
if (nightmodeChangeCheck == 'true') {
  toggleUnfoldArea('dropdown_content', 'dropbtnUnfoldButton')
}

// Group Form
function printGroupForm() {
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    toggleDropdown('dropdown_create_content');
    var html = `${addHeaderDynamicForm('Create Group')}
      <input class="input-login" placeholder="name" type="text" name="name" id="createGroup_groupName"/>
      <button class="button" onclick="groupHandler.createGroup()">Create</button>`
    showDynamicForm(container, html)
    closeDynamicFormListener()
  }
}

function openFeedbackForm() {
  toggleDropdown('dropdown_content')
  var html = `
    ${addHeaderDynamicForm('Update Task')}
    <textarea class="input-login" type="text" name="description" id="feedbackDescription" cols="40" rows="5"></textarea>
    <button class="button" onclick="createFeedback()">Update</button>`
  showDynamicForm(document.getElementById("dynamic-modal-content"), html)
  closeDynamicFormListener()
}

async function createFeedback() {
  const description = document.getElementById('feedbackDescription').value
  if (!description) return printErrorToast("EMPTY_FIELDS")
  const response = await requestHandler.sendRequest('createFeedback', ['description', description])
  if (response.ResponseCode != "OK") return
  closeDynamicForm()
  indexHandler.printIndexGroups()
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
        closeDynamicForm()
      }
    )
  }
}

function closeDynamicForm() {
  document.getElementById('dynamic-modal-content').innerHTML = '';
  document.getElementById('bg-modal-dynamicform').style.display = 'none';
  document.querySelector('html').style.overflow = 'auto';
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

addCheckboxListener('nightmode-checkbox', 'toggleNightmode')

panels.toggleActiveCheckboxListener('motdActiveCheckbox', 'motd') // Active MOTD
panels.toggleActiveCheckboxListener('appointmentActiveCheckbox', 'appointment') // Active Appointment
panels.toggleActiveCheckboxListener('queueActiveCheckbox', 'queue') // Active Queue
panels.toggleActiveCheckboxListener('weatherActiveCheckbox', 'weather') // Active Weather
panels.toggleActiveCheckboxListener('timetableActiveCheckbox', 'timetable') // Active Timetable
panels.toggleActiveCheckboxListener('morningroutineActiveCheckbox', 'morningroutine') // Active Morningroutine

panels.toggleUnfoldCheckboxListener('motdUnfoldedCheckbox', 'motd') // Unfold MOTD
panels.toggleUnfoldCheckboxListener('appointmentUnfoldedCheckbox', 'appointment') // Unfold Appointment
panels.toggleUnfoldCheckboxListener('queueUnfoldedCheckbox', 'queue') // Unfold Queue
panels.toggleUnfoldCheckboxListener('weatherUnfoldedCheckbox', 'weather') // Unfold Weather
panels.toggleUnfoldCheckboxListener('timetableUnfoldedCheckbox', 'timetable') // Unfold Timetable
panels.toggleUnfoldCheckboxListener('morningroutineUnfoldedCheckbox', 'morningroutine') // Unfold Morningroutine
