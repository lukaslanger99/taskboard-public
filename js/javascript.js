const DIR_SYSTEM = 'http://lukaslanger.bplaced.net/taskboard/'

function deleteUser(name, id) {
  var b = confirm("Are you sure you want to delete " + name + "?");
  if (b == true) {
    location.href = DIR_SYSTEM + "php/admin.inc.php?action=deleteUser&userID=" + id + "";
  }
}

function removeUserAccess(groupID, userID, userName) {
  var b = confirm("Are you sure you want to remove " + userName + "?");
  if (b == true) {
    location.href = DIR_SYSTEM + "php/action.php?action=removeUser&userID=" + userID + "&groupID=" + groupID;
  }
}

function showForm(id) {
  var element = document.getElementById(id);
  if (element) {
    document.getElementById(id).style.display = 'flex';
    document.querySelector('html').style.overflow = 'hidden';
  }
}

//Edit comment form
function openEditCommentForm(commentID, text) {
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    html = '\
          <div class="modal-header">\
            Update Comment\
            <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>\
          </div>\
          <form action="'+ DIR_SYSTEM + 'php/action.php?action=updateComment&commentID=' + commentID + '" autocomplete="off" method="post" >\
            <textarea class="input-login" type="text" name="text" cols="40" rows="1">'+ text + '</textarea>\
            <input class="submit-login" type="submit" name="updatecomment-submit" value="Update" />\
          </form>';
    container.innerHTML = html;
    container.style.height = '230px';

    document.getElementById('bg-modal-dynamicform').style.display = 'flex';
    closeDynamicFormListener()
  }
}

function printEditMailForm(mail) {
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    html = '\
          <div class="modal-header">\
            Update Mail\
            <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>\
          </div>\
          <div id="editmailform">\
            <form action="'+ DIR_SYSTEM + 'php/profile.inc.php?action=updateMail" autocomplete="off" method="post" >\
                <textarea class="input-login" type="text" name="mail" cols="40" rows="1">'+ mail + '</textarea>\
                <input class="submit-login" type="submit" name="updatemail-submit" value="Update" />\
            </form>\
          </div>';
    container.innerHTML = html;
    container.style.height = '180px';

    document.getElementById('bg-modal-dynamicform').style.display = 'flex';
    closeDynamicFormListener()
  }
}

function toggleDropdown(id) {
  var container = document.getElementById(id);
  if (container) {
    var containerDisplay = getComputedStyle(container).display;
    if (containerDisplay == 'none') {
      container.style.display = 'flex';
    } else if (containerDisplay == 'flex') {
      container.style.display = 'none';
    }
  }
}

function toggleUnfoldArea(targetId, buttonId, autoToggle = '') {
  var container = document.getElementById(targetId)
  var button = document.getElementById(buttonId)
  if (container) {
    var containerDisplay = getComputedStyle(container).display;
    if (containerDisplay == 'none') {
      container.style.display = 'flex'
      button.innerHTML = `<p><i class="fa fa-caret-up" aria-hidden="true"></i></p>`
    } else if (containerDisplay == 'flex' && autoToggle == '') {
      container.style.display = 'none'
      button.innerHTML = `<p><i class="fa fa-caret-down" aria-hidden="true"></i></p>`
    }
  }
}