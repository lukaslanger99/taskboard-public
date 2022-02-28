let panels = {
    getEntrys: async function (action) {
        const response = await fetch(
            `${DIR_SYSTEM}server/request.php?action=${action}`
        )
        return await response.json()
    },
    // Queue
    printQueueTasks: async function (queueTasks = '') {
        if (queueTasks == '') queueTasks = await this.getEntrys('getQueueTasks')
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
        const response = await fetch(
            `${DIR_SYSTEM}server/request.php?action=deleteQueueTask&id=${id}`
        )
        this.printQueueTasks(await response.json())
    },
    addQueueTask: async function () {
        var text = document.getElementById("queueItem").value
        if (text) {
            var url = `${DIR_SYSTEM}server/request.php?action=addQueueTask`
            var formData = new FormData()
            formData.append('text', text)
            formData.append('check', document.getElementById("queueHighprio").checked)
            const response = await fetch(
                url, { method: 'POST', body: formData }
            )
            document.getElementById("queueItem").value = ''
            document.getElementById("queueHighprio").checked = false
            this.printQueueTasks(await response.json())
        }
    },
    // Appointment
    printAppointments: async function (appointments = '') {
        if (appointments == '') appointments = await this.getEntrys('getAppointments')
        var html = '', toggle = false, title = ''
        if (appointments) {
            appointments.forEach(entry => {
                html += `<div class="${(toggle) ? `panel-item-content-item` : `panel-item-content-item__secondary`}">
                        <div class="panel-item-message-title">
                            ${entry.messageDate} - ${entry.messageTitleFormated}
                            <small>${entry.messageOwnerName} - ${entry.messageGroupName}</small>
                        </div>
                        ${(entry.messagePermission) ?
                        `<div 
                            class="panel-item-delete-button" 
                            onclick="panels.openEditAppointmentForm(${entry.messageID},'${entry.messageTitle}','${entry.messageDateFormFormat}')"
                            >
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </div>
                            <div class="panel-item-delete-button" onclick="panels.deleteAppointment(${entry.messageID})">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </div>`
                        :
                        ``
                    }   
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
            <table style="margin:0 auto 15px auto;">
                <tr>
                    ${groups}
                    <td>Date:</td>
                    <td>
                        <input type="date" id="appointmentDate" name="date">
                    </td>
                </tr>
            </table>
            <textarea class="input-login" id="appointmentTitle" placeholder="name" name="title" rows="1"></textarea>
            <input class="submit-login" type="submit" value="Create" onclick="panels.addAppointment()" />`
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    addAppointment: async function () {
        var group = document.getElementById("selectGroupID").value
        var date = document.getElementById("appointmentDate").value
        var title = document.getElementById("appointmentTitle").value
        if (group && date && title) {
            var url = `${DIR_SYSTEM}server/request.php?action=addAppointment`
            var formData = new FormData()
            formData.append('group', group)
            formData.append('date', date)
            formData.append('title', title)
            const response = await fetch(
                url, { method: 'POST', body: formData }
            )
            hideDynamicForm()
            this.printAppointments(await response.json())
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
        if (date && title) {
            var url = `${DIR_SYSTEM}server/request.php?action=editAppointment&id=${id}`
            var formData = new FormData()
            formData.append('date', date)
            formData.append('title', title)
            const response = await fetch(
                url, { method: 'POST', body: formData }
            )
            hideDynamicForm()
            this.printAppointments(await response.json())
        }
    },
    deleteAppointment: async function (id) {
        var a = confirm("Are you sure you want to delete this appointment?");
        if (a == true) {
            const response = await fetch(
                `${DIR_SYSTEM}server/request.php?action=deleteAppointment&id=${id}`
            )
            this.printAppointments(await response.json())
        }
    },
    printMotd: async function (motd = '') {
        if (motd == '') motd = await this.getEntrys('getMotd')
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
    openAddMotdForm: async function() {
        const groups = await printGroupDropdown()
        var html = `${addHeaderDynamicForm('Create Message of the Day')}
            <table style="margin:0 auto 15px auto;">
                <tr>
                ${groups}
                </tr>
            </table>
            <textarea class="input-login" id="motdTitle" placeholder="name" name="title" rows="1"></textarea>
            <input class="submit-login" type="submit" value="Create" onclick="panels.addMotd()" />`
    showDynamicForm(document.getElementById("dynamic-modal-content"), html)
    closeDynamicFormListener()
    },
    addMotd: async function() {
        var group = document.getElementById("selectGroupID").value
        var title = document.getElementById("motdTitle").value
        if (group && title) {
            var url = `${DIR_SYSTEM}server/request.php?action=addMotd`
            var formData = new FormData()
            formData.append('group', group)
            formData.append('title', title)
            const response = await fetch(
                url, { method: 'POST', body: formData }
            )
            hideDynamicForm()
            this.printMotd(await response.json())
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
        if (title) {
            var url = `${DIR_SYSTEM}server/request.php?action=editMotd&id=${id}`
            var formData = new FormData()
            formData.append('title', title)
            const response = await fetch(
                url, { method: 'POST', body: formData }
            )
            hideDynamicForm()
            this.printMotd(await response.json())
        }
    },
    deleteMotd: async function (id) {
        var a = confirm("Are you sure you want to delete this motd?");
        if (a == true) {
            const response = await fetch(
                `${DIR_SYSTEM}server/request.php?action=deleteMotd&id=${id}`
            )
            this.printMotd(await response.json())
        }
    }
}