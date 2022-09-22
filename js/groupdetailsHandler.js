let groupdetailsHandler = {
    printGroupdetails: async function () {
        const response = await requestHandler.sendRequest('getGroupData', ['groupID', document.URL.replace(/.*id=([^&]*).*|(.*)/, '$1')])
        if (response.ResponseCode != 'OK') location.href = DIR_SYSTEM
        const data = response.data
        headerHTML = this.printHeader(data.groupName)
        buttonsHTML = this.printButtons(data.groupID, data.groupOwner)
        tasksHTML = this.printTasks(data.tasks)
        document.getElementById('groupdetails').innerHTML = `${headerHTML}${buttonsHTML}${tasksHTML}`
    },
    printHeader: function (groupName) {
        return `
            <div class="taskdetails__header">
                <div class="taskdetails__header__image">
                    <img alt="" src="">
                </div>
                <div class="taskdetails__header__main">
                    <h1 class="taskdetails__title">${groupName}</h1>
                </div>
            </div>`
    },
    printButtons: function (groupID, groupOwner) {
        return `
            <div class="taskdetails__buttons">
                <button onclick="groupHandler.openUsersPopup(${groupID})">Users</button>
                <button onclick="labelHandler.openGroupLabelsPopup(${groupID})">Labels</button>
                <button onclick="groupHandler.openInvitesPopup(${groupID})">Invites</button>
                <button onclick="groupHandler.openSettingsPopup(${groupID})">Settings</button>
                ${(groupOwner)
                ? `<button onclick="groupHandler.deleteGroup(${groupID})">Delete Group</button>`
                : `<button onclick="groupHandler.leaveGroup(${groupID})">Leave Group</button>`}
            </div>`
    },
    printTasks: function (tasks) {
        if (!tasks) return ''
        var tasksHTML = ''
        tasks.forEach(task => {
            tasksHTML += `
                <div class="taskdetails__subtask">
                    <p><i class="fa fa-square" style="color: ${taskdetailsHandler.getPriorityColor(task.taskPriority)};"></i></p>
                    <p><a href="${DIR_SYSTEM}php/details.php?action=taskDetails&id=${task.taskID}">ID_${task.taskID}</a></p>
                    <p class="taskdetails__subtask__title">${task.taskTitle}</p>
                    <p>
                        ${(task.taskStatus == 'open')
                    ? `<div class="status status__open">OPEN</div>`
                    : `<div class="status status__resolved">RESOLVED</div>`}
                    </p>
                </div>
                <hr>`
        })
        return `<div class="groupdetails__tasks">${tasksHTML}</div>`
    }
}