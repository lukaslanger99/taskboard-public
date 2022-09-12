let panels = {
    // Queue
    printQueueTasks: async function (queueTasks = '') {
        if (queueTasks == '') {
            const response = await requestHandler.sendRequest('getQueueTasks')
            queueTasks = response.data
        }
        var html = '', toggle = false, title = ''
        if (queueTasks) {
            queueTasks.forEach(entry => {
                html += `<div class="${(toggle) ? `panel-item-content-item` : `panel-item-content-item__secondary`}">
                    <div class="panel-item-message-title">${entry.messageTitle}</div>
                        <div class="panel-item-check-button" onclick="panels.deleteQueueTask(${entry.messageID})">
                            <i class="fa fa-check" aria-hidden="true"></i>
                        </div>
                    </div>`
                toggle = !toggle
            })
            document.getElementById('queuePanelContentArea').innerHTML = html
            if (queueTasks.length == 1) title = `Queue (1 Task)`
            else if (queueTasks.length > 1) title = `Queue (${queueTasks.length} Tasks)`
        } else {
            title = `Queue`
            document.getElementById('queuePanelContentArea').innerHTML = ''
        }
        document.getElementById('queuePanelTitle').innerHTML = title
    },
    deleteQueueTask: async function (id) {
        const response = await requestHandler.sendRequest('deleteQueueTask', ['id', id])
        this.printQueueTasks(response.data)
    },
    addQueueTask: async function () {
        var text = document.getElementById("queueItem").value
        if (!text) return printErrorToast("EMPTY_FIELDS")
        const response = await requestHandler.sendRequest('addQueueTask', ['text', text], ['check', document.getElementById("queueHighprio").checked])
        document.getElementById("queueItem").value = ''
        document.getElementById("queueHighprio").checked = false
        this.printQueueTasks(response.data)
    },
    // Morningroutine
    printMorningroutineTasks: async function () {
        const response = await requestHandler.sendRequest('getUnfinishedMorningroutineTasks')
        const morningroutineTasks = response.data
        var html = '', toggle = false
        if (morningroutineTasks) {
            morningroutineTasks.forEach(entry => {
                html += `<div class="${(toggle) ? `panel-item-content-item` : `panel-item-content-item__secondary`}">
                    <div class="panel-item-message-title">${entry.entryTitle}</div>
                        <div class="panel-item-check-button" onclick="panels.completeMorningroutineTask(${entry.entryID})">
                            <i class="fa fa-check" aria-hidden="true"></i>
                        </div>
                    </div>`
                toggle = !toggle
            })
            document.getElementById('morningroutinePanelContentArea').innerHTML = html
        } else {
            document.getElementById('morningroutinePanelContentArea').innerHTML = ''
        }
        document.getElementById('morningroutinePanelTitle').innerHTML = 'Morningroutine'
    },
    addMorningroutineTask: async function () {
        var text = document.getElementById("morningroutineItem").value
        if (!text) return printErrorToast("EMPTY_FIELDS")
        await requestHandler.sendRequest('addMorningroutineTask', ['text', text])
        document.getElementById("morningroutineItem").value = ''
        this.printMorningroutineTasks()
    },
    completeMorningroutineTask: async function (id) {
        const response = await requestHandler.sendRequest('completeMorningroutineTask', ['id', id])
        this.printMorningroutineTasks()
    },
    resetMorningroutine: async function () {
        const response = await requestHandler.sendRequest('resetMorningroutine')
        this.printMorningroutineTasks()
    },
    showMorningroutinePopup: async function () {
        const response = await requestHandler.sendRequest('getAllMorningroutineTasks')
        const morningroutineTasks = response.data
        var popupHTML = ``
        if (morningroutineTasks) {
            var flag = false
            morningroutineTasks.forEach(task => {
                (flag) ? popupHTML += `<hr class="solid">` : flag = true
                popupHTML += `
                    <div class="label__item draggable__item" draggable="true" data-type="${task.entryID}">
                        <div class="label__left">
                            <div>${task.entryTitle}</div>
                        </div>
                        <div class="label__right">
                            <i class="fa fa-trash fa-2x" aria-hidden="true" onclick="panels.deleteMorningroutineTask(${task.entryID})"></i>
                        </div>
                    </div>`
            });
        }
        var container = document.getElementById("dynamic-modal-content")
        var html = `<div class="modal-header">
              <div class="modal__header__left">Morningroutine</div>
              <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>
            </div>
            <div class="label__list draggable__container">${popupHTML}</div>`
        showDynamicForm(container, html)
        closeDynamicFormListener()
        addDraggableHelper('updateMorningroutineOrder')
    },
    deleteMorningroutineTask: async function (entryID) {
        if (!confirm("Are you sure you want to delete this task?")) return
        await requestHandler.sendRequest('deleteMorningroutineTask', ['entryID', entryID])
        this.showMorningroutinePopup()
    },
    // Appointment
    printAppointments: async function () {
        const response = await requestHandler.sendRequest('getAppointments')
        const appointments = response.data
        var html = '', toggle = false, title = ''
        if (appointments) {
            appointments.forEach(entry => {
                html += `<div class="${(entry.currentMonth) ? 'timetable__content__block' : 'timetable__panel__prevtask'}">
                    <div class="timetable__content__task__row">
                        <div class="timetable__content__task__date">${entry.messageDate}</div>
                        ${(entry.messagePermission) ?
                        `<div class="appointment__content__task__invisible__buttons">
                            <div 
                                class="appointment__invisible__button" 
                                onclick="panels.openEditAppointmentForm(${entry.messageID},'${entry.messageTitle}','${entry.messageDateFormFormat}')"
                            >
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </div>
                            <div class="appointment__invisible__button" onclick="panels.deleteAppointment(${entry.messageID})">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </div>
                        </div>`
                        :
                        ``
                    }  
                        </div>
                    <div class="timetable__content__task__time">${entry.timeStart}
                    ${entry.timeEnd != '-' ? `- ${entry.timeEnd}` : ``}
                    </div>
                    <div class="timetable__content__task__text">
                        ${entry.messageTitleFormated}
                    </div>
                    <div class="appointment__text__author">${entry.messageOwnerName} - ${entry.messageGroupName}</div>
                </div>`
                toggle = !toggle
            })
            document.getElementById('appointmentPanelContentArea').innerHTML = html
            if (appointments.length == 1) title = `Appointments (1 Appointment)`
            else if (appointments.length > 1) title = `Appointments (${appointments.length} Appointments)`
        } else {
            title = `Appointments`
            document.getElementById('appointmentPanelContentArea').innerHTML = ''
        }
        document.getElementById('appointmentPanelTitle').innerHTML = title
    },
    openAddAppointmentForm: async function () {
        const groups = await printGroupDropdown()
        html = `${addHeaderDynamicForm('Create Appointment')}
            <div class="popop__dropdowns">
                ${groups}
                <p>Date:</p>
                <p><input type="date" id="appointmentDate" name="date"></p>
            </div>
            <div class="popop__dropdowns">
                <p>Start:</p>
                <p><input type="time" name="start" id="start" value="00:00"/></p>
                <p>End (Optional):</p>
                <p><input type="time" name="end" id="end"/></p>
            </div>
            <textarea class="input-login" id="appointmentTitle" placeholder="name" name="title" rows="1"></textarea>
            <input class="submit-login" type="submit" value="Create" onclick="panels.addAppointment()" />`
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    openAppointmentCalendar: async function (month = -1, year = -1) {
        var title = 'Calendar', buttons = `<button onclick="panels.openAddAppointmentForm()">Create Appointment</button>`
        var content = `
            <div class="calendar__day__head">Monday</div>
            <div class="calendar__day__head">Tuesday</div>
            <div class="calendar__day__head">Wednesday</div>
            <div class="calendar__day__head">Thursday</div>
            <div class="calendar__day__head">Friday</div>
            <div class="calendar__day__head">Saturday</div>
            <div class="calendar__day__head">Sunday</div>
            `

        if (month == -1 && year == -1) var currentDate = new Date(), month = currentDate.getMonth(), year = currentDate.getFullYear()
        const response = await requestHandler.sendRequest('getAppointmentsFromMonth', ['month', month + 1], ['year', year])
        const appoinments = response.data
        var days = getDaysInMonth(month, year)
        var offsetFirstDay = days[0].getDay() - 1 // Sunday - Saturday : 0 - 6
        var boxcounter = offsetFirstDay

        for (let i = 0; i < offsetFirstDay; i++) {
            content += '<div class="calendar__day__box"></div>'
        }

        days.forEach(day => {
            var formatedDate = day.getFullYear() + '-' + ((day.getMonth() < 10) ? '0' : '') + (day.getMonth() + 1) + '-' + ((day.getDate() < 10) ? '0' : '') + day.getDate()
            var appoinmentHTML = ''
            if (appoinments) {
                appoinments.forEach(appoinment => {
                    if (appoinment.messageDateFormFormat == formatedDate) {
                        appoinmentHTML += '<div class="calendar__day__box__entry">' + appoinment.messageTitle + '</div>'
                    }
                });
            }
            content += '<div class="calendar__day__box border__black"><div class="calendar__day__box__head">' + day.getDate() + '</div>' + appoinmentHTML + '</div>'
            boxcounter++
        });

        while (boxcounter % 7 != 0) {
            content += '<div class="calendar__day__box"></div>'
            boxcounter++
        }

        var prevMonth, prevYear, nextMonth, nextYear
        if (month == 0) prevMonth = 11, prevYear = year - 1, nextMonth = month + 1, nextYear = year
        else if (month == 11) prevMonth = month - 1, prevYear = year, nextMonth = 0, nextYear = year + 1
        else prevMonth = month - 1, prevYear = year, nextMonth = month + 1, nextYear = year

        var html = `
            <div class="modal-header">
                <div class="modal__header__left">${title}</div>
                <div class="modal__header__right">${buttons}</div>
                <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>
            </div>
            <div class="appointment__content">
                <div class="calendar__month__switch">
                    <div class="calendar__changemonth__btn" onclick="panels.openAppointmentCalendar(${prevMonth}, ${prevYear})">
                        <i class="fa fa-arrow-left"></i>
                        ${getMonthNameByNumber(prevMonth)}, ${prevYear}
                    </div>
                    <div class="calendar__changemonth__btn" onclick="panels.openAppointmentCalendar(${nextMonth}, ${nextYear})">
                        ${getMonthNameByNumber(nextMonth)}, ${nextYear}
                        <i class="fa fa-arrow-right"></i>
                    </div>
                </div>
                <div class="calendar__day__area">
                    ${content}
                </div>
            </div>`
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    addAppointment: async function () {
        var group = document.getElementById("selectGroupID").value
        var date = document.getElementById("appointmentDate").value
        var title = document.getElementById("appointmentTitle").value
        var start = document.getElementById("start").value
        var end = document.getElementById("end").value
        if (group && date && title && start) {
            await requestHandler.sendRequest('addAppointment', ['group', group], ['date', date], ['title', title], ['start', start], ['end', end])
            hideDynamicForm()
            this.printAppointments()
        }
    },
    openEditAppointmentForm: function (id, title, date) {
        html = `<div class="modal-header">Update Appointment<i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i></div>
            <table style="margin:0 auto 15px auto;">
                <tr>
                    <td>Date:</td>
                    <td>
                        <input type="date" id="appointmentDate" name="date" value="${date}">
                    </td>
                </tr>
            </table>
            <textarea class="input-login" type="text" id="appointmentTitle" name="title" cols="40" rows="1">${title}</textarea>
            <input class="submit-login" type="submit" value="Update" onclick="panels.editAppointment(${id})" />`
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    editAppointment: async function (id) {
        var date = document.getElementById("appointmentDate").value
        var title = document.getElementById("appointmentTitle").value
        if (!(date && title)) return printErrorToast("EMPTY_FIELDS")
        await requestHandler.sendRequest('editAppointment', ['id', id], ['date', date], ['title', title])
        hideDynamicForm()
        this.printAppointments()
    },
    deleteAppointment: async function (id) {
        if (!confirm("Are you sure you want to delete this appointment?")) return
        await requestHandler.sendRequest('deleteAppointment', ['id', id])
        this.printAppointments()
    },
    printMotd: async function (motd = '') {
        if (motd == '') {
            const response = await requestHandler.sendRequest('getMotd')
            motd = response.data
        }
        var html = '', toggle = false, title = ''
        if (motd) {
            motd.forEach(entry => {
                html += `<div class="${(toggle) ? `panel-item-content-item` : `panel-item-content-item__secondary`} ${(entry.messageRedRounded) ? `redrounded` : ``}">
                        <div class="panel-item-message-title">
                            ${entry.messageDate} - ${entry.messageTitleFormated}
                            <small>${entry.messageOwnerName} - ${entry.messageGroupName}</small>
                        </div>
                        ${(entry.messagePermission) ?
                        `<div 
                            class="panel-item-delete-button" 
                            onclick="panels.openEditMotdForm(${entry.messageID},'${entry.messageTitle}')"
                            >
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </div>
                            <div class="panel-item-delete-button" onclick="panels.deleteMotd(${entry.messageID})">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </div>`
                        :
                        ``
                    }   
                    </div>`
                toggle = !toggle
            })
            document.getElementById('motdPanelContentArea').innerHTML = html
            title = `Messages of the Day (${motd.length} MOTD)`
        } else {
            title = `Messages of the Day`
            document.getElementById('motdPanelContentArea').innerHTML = ''
        }
        document.getElementById('motdPanelTitle').innerHTML = title
    },
    openAddMotdForm: async function () {
        const groups = await printGroupDropdown()
        var html = `${addHeaderDynamicForm('Create Message of the Day')}
            <div class="popop__dropdowns">${groups}</div>
            <textarea class="input-login" id="motdTitle" placeholder="name" name="title" rows="1"></textarea>
            <input class="submit-login" type="submit" value="Create" onclick="panels.addMotd()" />`
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    addMotd: async function () {
        var group = document.getElementById("selectGroupID").value
        var title = document.getElementById("motdTitle").value
        if (group && title) {
            const response = await requestHandler.sendRequest('addMotd', ['group', group], ['title', title])
            hideDynamicForm()
            this.printMotd(response.data)
        }
    },
    openEditMotdForm: function (id, title) {
        html = `<div class="modal-header">Edit Message<i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i></div>
            <textarea class="input-login" id="motdTitle" type="text" name="title" cols="40" rows="1">${title}</textarea>
            <input class="submit-login" type="submit" value="Update" onclick="panels.editMotd(${id})"/>`;
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    editMotd: async function (id) {
        var title = document.getElementById("motdTitle").value
        if (!title) return printErrorToast("EMPTY_FIELDS")
        const response = await requestHandler.sendRequest('editMotd', ['id', id])
        hideDynamicForm()
        this.printMotd(response.data)
    },
    deleteMotd: async function (id) {
        if (!confirm("Are you sure you want to delete this motd?")) return
        const response = await requestHandler.sendRequest('deleteMotd', ['id', id])
        hideDynamicForm()
        this.printMotd(response.data)
    },
    toggleUnfoldCheckboxListener: async function (id, type) {
        var checkboxElement = document.getElementById(id)
        if (checkboxElement) {
            checkboxElement.addEventListener("click",
                async () => {
                    return await requestHandler.sendRequest('toggleUnfoldPanel', ['type', type], ['checked', checkboxElement.checked])
                }
            )
        }
    },
    toggleActiveCheckboxListener: async function (id, type) {
        var checkboxElement = document.getElementById(id)
        if (checkboxElement) {
            checkboxElement.addEventListener("click",
                async () => {
                    return await requestHandler.sendRequest('toggleActivePanel', ['type', type], ['checked', checkboxElement.checked])
                }
            )
        }
    }
}

/**
 * @param {int} The month number, 0 based
 * @param {int} The year, not zero based, required to account for leap years
 * @return {Date[]} List with date objects for each day of the month
 */
function getDaysInMonth(month, year) {
    var date = new Date(year, month, 1);
    var days = [];
    while (date.getMonth() === month) {
        days.push(new Date(date));
        date.setDate(date.getDate() + 1);
    }
    return days;
}

/**
 * @param {int} The month number, 0 based
 * @return {String} Month name
 */
function getMonthNameByNumber(month) {
    const days = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
    return days[month]
}
