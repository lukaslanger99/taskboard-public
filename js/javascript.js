const DIR_SYSTEM = 'http://lukaslanger.bplaced.net/taskboard/'

function leaveGroup(groupID) {
  var a = confirm("Are you sure you want to leave this group?");
  if (a == true) {
    location.href = DIR_SYSTEM + "php/action.php?action=leaveGroup&groupID=" + groupID;
  }
}

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

//Edit group form
function openEditGroupForm(groupID, name, priority, archiveTime) {
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    html = '\
          <div class="modal-header">\
            Update Group\
            <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>\
          </div>\
          <form action="'+ DIR_SYSTEM + 'php/action.php?action=updateGroup&id=' + groupID + '" autocomplete="off" method="post" >\
          <table style="margin:0 auto 15px auto;">\
              <tr>\
                  <td>Name:</td>\
                  <td>\
                      <input type="text" name="name" value="'+ name + '">\
                  </td>\
              </tr>\
              <tr>\
                  <td>Priority:</td>\
                  <td>\
                      <input type="number" name="priority" min="1" max="1000" value="'+ priority + '">\
                  </td>\
              </tr>\
              <tr>\
              <td>Number of days till task archived:</td>\
                  <td>\
                      <input type="number" name="archivetime" min="1" max="365" value="'+ archiveTime + '">\
                  </td>\
              </tr>\
          </table>\
            <input class="submit-login" type="submit" name="updategroup-submit" value="Update" />\
          </form>';
    container.innerHTML = html;
    container.style.height = '230px';

    document.getElementById('bg-modal-dynamicform').style.display = 'flex';
    var faCloseDynamicform = document.getElementById('fa-close-dynamicform');
    if (faCloseDynamicform) {
      faCloseDynamicform.addEventListener('click',
        function () {
          var container = document.getElementById("dynamic-modal-content");
          if (container) {
            container.innerHTML = '';
            document.getElementById('bg-modal-dynamicform').style.display = 'none';
          }
        }
      )
    }
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
    var faCloseDynamicform = document.getElementById('fa-close-dynamicform');
    if (faCloseDynamicform) {
      faCloseDynamicform.addEventListener('click',
        function () {
          var container = document.getElementById("dynamic-modal-content");
          if (container) {
            container.innerHTML = '';
            document.getElementById('bg-modal-dynamicform').style.display = 'none';
          }
        }
      )
    }
  }
}

function openShowUsersPopup() {
  var container = document.getElementById("bg-modal-groupusers");
  if (container) {
    container.style.display = 'flex';
    var faCloseShowUSers = document.getElementById('fa-close-groupusers');
    if (faCloseShowUSers) {
      faCloseShowUSers.addEventListener('click',
        function () {
          container.style.display = 'none';
        }
      )
    }
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

    var faCloseDynamicform = document.getElementById('fa-close-dynamicform');
    if (faCloseDynamicform) {
      faCloseDynamicform.addEventListener('click',
        function () {
          var container = document.getElementById("dynamic-modal-content");
          if (container) {
            container.innerHTML = '';
            document.getElementById('bg-modal-dynamicform').style.display = 'none';
          }
        }
      )
    }
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
      button.style.webkitTransform = 'rotate(180deg)'
    } else if (containerDisplay == 'flex' && autoToggle == '') {
      container.style.display = 'none'
      button.style.webkitTransform = 'rotate(0deg)'
    }
  }
}

async function groupUnfoldCheckboxListener(groupID) {
  var checkboxElement = document.getElementById('groupUnfoldCheckbox')
  checkboxElement.addEventListener('click',
    async () => {
      const response = await fetch(
        `${DIR_SYSTEM}server/request.php?action=toggleUnfoldGroup&id=${groupID}&checked=${checkboxElement.checked}`
      )
      return await response.json()
    })
}