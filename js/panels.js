let panels = {
    getEntrys: async (action) => {
        const response = await fetch(
            `${DIR_SYSTEM}server/request.php?action=${action}`
        )
        return await response.json()
    },
    // Queue
    printQueueTasks: async (queueTasks = '') => {
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
    addQueueTask: async () => {
        var url = `${DIR_SYSTEM}server/request.php?action=addQueueTask`
        var formData = new FormData()
        formData.append('text', document.getElementById("queueItem").value)
        formData.append('check', document.getElementById("queueHighprio").checked)
        const response = await fetch(
            url, { method: 'POST', body: formData }
        )
        document.getElementById("queueItem").value = ''
        document.getElementById("queueHighprio").checked = false
        this.printQueueTasks(await response.json())
    },
    // Appointment
    printAppointments: async (appointments = '') => {
        if (appointments == '') appointments = await this.getEntrys('getAppointments')
        console.log(appointments)
        var html = '', toggle = false, title = ''
        if (appointments) {
            appointments.forEach(entry => {
                html += `<div class="${(toggle) ? `panel-item-content-item` : `panel-item-content-item__secondary`}"">
                        <div class="${(toggle) ? `panel-item-message-title__redrounded` : `panel-item-message-title`}">
                            ${entry.messageDate}-${entry.messageTitleFormated}
                            <small>${entry.messageOwnerName} - ${entry.messageGroupName}</small>
                        </div>
                        ${(entry.messageGroupOwnerCheck) ?
                        `<div 
                            class="panel-item-delete-button" 
                            onclick="panels.openEditAppointmentForm(${entry.messageID},'${entry.messageTitle}','${entry.messageDateFormFormated}')"
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
    openEditAppointmentForm: (id, title, date) => {
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

    },
    deleteAppointment: async function (id) {

    }
}