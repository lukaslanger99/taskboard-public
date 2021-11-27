const DIR_SYSTEM = 'http://lukaslanger.bplaced.net/taskboard/';

function deleteTask(id) {
  var b = confirm("Are you sure you want to delete Task id:" + id + "?");
  if (b == true) {
    location.href = DIR_SYSTEM + "php/action.php?action=deleteTask&id=" + id;
  }
}

function deleteComment(id, taskId) {
  var r = confirm("Are you sure you want to delete this Comment?");
  if (r == true) {
    location.href = DIR_SYSTEM + "php/action.php?action=deleteComment&id=" + id + "&taskId=" + taskId;
  }
}

function deleteGroup(groupID) {
  var a = confirm("Are you sure you want to delete this group?");
  if (a == true) {
    location.href = DIR_SYSTEM + "php/action.php?action=deleteGroup&id=" + groupID;
  }
}

function leaveGroup(groupID) {
  var a = confirm("Are you sure you want to leave this group?");
  if (a == true) {
    location.href = DIR_SYSTEM + "php/action.php?action=leaveGroup&groupID=" + groupID;
  }
}

function deleteMessage(id) {
  var r = confirm("Are you sure you want to delete this Message?");
  if (r == true) {
    location.href = DIR_SYSTEM + "php/action.php?action=deleteMessage&id=" + id;
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

function printEditMessageForm(messageID, messageTitle) {
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    var html = '\
    <div class="modal-header">\
        Edit Message\
        <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>\
    </div>\
    <form action="'+ DIR_SYSTEM + 'php/action.php?action=updateMessage&id=' + messageID + '" autocomplete="off" method="post" >\
        <textarea class="input-login" type="text" name="title" cols="40" rows="1">'+ messageTitle + '</textarea>\
        <input class="submit-login" type="submit" name="updatemessage-submit" value="Update" />\
    </form>';
    container.innerHTML = html;
    container.style.height = '210px';

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

function printEditAppointmentForm(messageID, messageTitle, date) {
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    html = '\
          <div class="modal-header">\
            Update Appointment\
            <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>\
          </div>\
          <form action="'+ DIR_SYSTEM + 'php/action.php?action=updateAppointment&id=' + messageID + '" autocomplete="off" method="post" >\
              <table style="margin:0 auto 15px auto;">\
                  <tr>\
                      <td>Date:</td>\
                      <td>\
                          <input type="date" name="date" value="'+ date + '">\
                      </td>\
                  </tr>\
              </table>\
              <textarea class="input-login" type="text" name="title" cols="40" rows="1">'+ messageTitle + '</textarea>\
              <input class="submit-login" type="submit" name="updatemessage-submit" value="Update" />\
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
        })
    }
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
              <td>Times till task archived:</td>\
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

function printEditRTForm(messageID, messageTitle, messageWeekday, messageQuantity) {
  var weekday = '';
  if (messageWeekday == 'everyday') {
    weekday += '<option selected="selected" value="everyday">Everyday</option>';
  } else {
    weekday += '<option value="everyday">Everyday</option>';
  }
  if (messageWeekday == 'Mon') {
    weekday += '<option selected="selected" value="Mon">Monday</option>';
  } else {
    weekday += '<option value="Mon">Monday</option>';
  }
  if (messageWeekday == 'Tue') {
    weekday += '<option selected="selected" value="Tue">Tuesday</option>';
  } else {
    weekday += '<option value="Tue">Tuesday</option>';
  }
  if (messageWeekday == 'Wed') {
    weekday += '<option selected="selected" value="Wed">Wednesday</option>';
  } else {
    weekday += '<option value="Wed">Wednesday</option>';
  }
  if (messageWeekday == 'Thu') {
    weekday += '<option selected="selected" value="Thu">Thursday</option>';
  } else {
    weekday += '<option value="Thu">Thursday</option>';
  }
  if (messageWeekday == 'Fri') {
    weekday += '<option selected="selected" value="Fri">Friday</option>';
  } else {
    weekday += '<option value="Fri">Friday</option>';
  }
  if (messageWeekday == 'Sat') {
    weekday += '<option selected="selected" value="Sat">Saturday</option>';
  } else {
    weekday += '<option value="Sat">Saturday</option>';
  }
  if (messageWeekday == 'Sun') {
    weekday += '<option selected="selected" value="Sun">Sunday</option>';
  } else {
    weekday += '<option value="Sun">Sunday</option>';
  }
  var quantity = '';
  if (messageQuantity == 'everyweek') {
    quantity += '<option selected="selected" value="everyweek">Everyweek</option>';
  } else {
    quantity += '<option value="everyweek">Everyweek</option>';
  }
  if (messageQuantity == 'odd') {
    quantity += '<option selected="selected" value="odd">Odd</option>';
  } else {
    quantity += '<option value="odd">Odd</option>';
  }
  if (messageQuantity == 'even') {
    quantity += '<option selected="selected" value="even">Even</option>';
  } else {
    quantity += '<option value="even">Even</option>';
  }
  var container = document.getElementById("dynamic-modal-content");
  if (container) {
    html = '\
            <div class="modal-header">\
              Update RT\
              <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>\
            </div>\
            <div id="editrtform">\
            <form action="'+ DIR_SYSTEM + 'php/action.php?action=updateRT&id=' + messageID + '" autocomplete="off" method="post" >\
              <table id="rtWeekdayAndQuantity" style="margin:0 auto 15px auto;">\
                <tr>\
                    <td>Weekday:</td>\
                    <td>\
                        <div class="select">\
                            <select class="weekday" name="weekday">'+ weekday + '</select>\
                        </div>\
                    </td>\
                    <td>Quantity:</td>\
                    <td>\
                        <div class="select">\
                            <select class="quantity" name="quantity">'+ quantity + '</select>\
                        </div>\
                    </td>\
                </tr>\
              </table>\
              <textarea class="input-login" type="text" name="title" cols="40" rows="1">'+ messageTitle + '</textarea>\
              <input class="submit-login" type="submit" name="updatemessage-submit" value="Update" />\
            </form>\
            </div>';
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