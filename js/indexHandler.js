let indexHandler = {
    getGroupsWithTasks: async function () {
        const response = await fetch(
            `${DIR_SYSTEM}server/request.php?action=getActiveGroupsWithTasks`
        )
        return await response.json()
    },
    printIndexGroups: async function () {
        const container = document.getElementById("group__boxes").innerHTML
        if (!container) return
        const groups = await this.getGroupsWithTasks()
        html = ''
        if (groups) {
            groups.forEach(group => {
                html += this.printGroup(group)
            });
        } else {
            html += `<div = class="emptypage-modal">
                    <div class="emptypage">Nothing to do, go create some tasks or groups and start working :-)</div>
                </div>`
        }
        container = html
        executeScriptElements(document.getElementsByTagName("body")[0])
    },
    printGroup: function (group) {
        if (!group.unarchivedTasks) return ``
        var groupname = group.groupName, groupID = group.groupID
        var groupContentID = 'groupContent_' + groupname, groupUnfoldButtonID = 'groupUnfoldButton_' + groupname
        const openTasks = group.unarchivedTasks.filter((task) => task.taskStatus == 'open' && !task.activeLabels)
        var mobileLine = ''
        if (openTasks) {
            mobileLine = `(${openTasks.length}) Open`
        }
        var html = `<div class="group-box">
            <div class="group-top-bar">
                <div class="group_top_bar_left">
                    <a href="php/details.php?action=groupDetails&id=${groupID}"><p>${groupname}</p></a>
                </div>
                <div class="group_top_bar_right">
                        <p>${mobileLine}</p>
                    <div class="group_dropbtn" id="${groupUnfoldButtonID}" onclick="toggleUnfoldArea(\'${groupContentID}\',\'${groupUnfoldButtonID}\')">
                        <p><i class="fa fa-caret-down" aria-hidden="true"></i></p>
                    </div>
                </div>
            </div>
            <div class="group-content" id="groupContent_${groupname}">`
        html += this.printGroupTaskColumn(openTasks, 'Open')
        if (group.labels) {
            group.labels.forEach(label => {
                var labeltasks = []
                group.unarchivedTasks.forEach(task => {
                    if (task.activeLabels) {
                        task.activeLabels.forEach(taskLabel => {
                            if (taskLabel.labelID == label.labelID) {
                                labeltasks.push(task)
                            }
                        });
                    }
                });
                html += this.printGroupTaskColumn(labeltasks, label.labelName)
            });
        }
        html += this.printGroupTaskColumn(group.unarchivedTasks.filter((task) => task.taskStatus == 'resolved'), 'Resolved')
        html += `</div>
            </div>`
        if (group.unfolded) {
            html += `<script>toggleUnfoldArea('${groupContentID}','${groupUnfoldButtonID}', 'true')</script>`
        }
        return html
    },
    printGroupTaskColumn: function (tasks, title) {
        html = `<div class="single-content">
            <div class="single-top-bar">
                <p>${title} ${(tasks.length) ? `(${tasks.length})` : ``}</p >
            </div > `
        if (tasks) {
            tasks.forEach(task => {
                html += this.printTask(task)
            });
        }
        html += `</div>`
        return html
    },
    printTask: function (task) {
        html = `<a href="${DIR_SYSTEM}php/details.php?action=taskDetails&id=${task.taskID}">
            <div class="box">
                <div class="priority" style="background-color: ${task.taskPriorityColor};"></div>
                <div class="content">
                        <div class="text">${task.taskTitle}</div>
                        <div class="emptyspace">&nbsp;</div>
                        <div class="bottom">
                            <div class="label bottom_label">id_${task.taskID}</div>`
        if (task.taskAssignedBy) html += `<div class="label bottom_label">${task.taskAssignedBy}</div>`
        if (task.taskStatus == 'open') {
            if (task.dateDiff == 0) html += `<div class="label new_label">NEW</div>`
            else if (task.dateDiff > 31) html += `<div class="label bottom_label" style="background-color:red;color:#fff;">${task.dateDiff}</div>`
            else html += `<div class="label bottom_label">${task.dateDiff}</div>`
        }
        if (task.activeLabels) {
            task.activeLabels.forEach(label => {
                html += `<div class="label" style="background-color: ${label.labelColor};">${label.labelName}</div>`
            });
        }
        if (task.numberOfSubtasks) {
            html += `<div class="label subtask_label">${(task.numberOfSubtasks > 1) ? `${task.numberOfSubtasks} Subtasks` : `1 Subtask`}</div>`
        }
        html += `</div>
                    </div>
                </div>
            </a>`
        return html
    }
}