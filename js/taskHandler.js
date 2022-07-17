let taskHandler = {
    openCreateTaskForm: async function (type, parentID = 0, toggleContentDropdown = true) {
        if (toggleContentDropdown && type == 'task') toggleDropdown('dropdown_create_content')
        var html = `${(type == 'task') ? addHeaderDynamicForm('Create Task') : addHeaderDynamicForm('Create Task')}
        <table style="margin:0 auto 15px auto;">
            <tr>
                <td>Priority:</td>
                <td>
                  <div class="select">
                    <select name="priority" id="taskprio">
                      <option value="1">Low</option>
                      <option selected="selected" value="2">Normal</option>
                      <option value="3">High</option>
                    </select>
                  </div>
                </td>
                ${(type == 'task') ? await printGroupDropdown(parentID) : ''}
            </tr>
            </table>
            <textarea class="input-login" placeholder="title" id="tasktitle" name="title" cols="40" rows="1"></textarea>
            <textarea class="input-login" placeholder="description" id="taskdescription" name="description" cols="40" rows="5"></textarea>
            <div class="createanother__bottom">
                <div class="createanother__left"></div>
                <div class="createanother__center">
                    <input 
                        style="margin-left:25%;" 
                        class="submit-login" 
                        type="submit" 
                        name="createtask-submit" 
                        value="Create" 
                        onclick="taskHandler.createTask('${type}'${(type == 'subtask') ? `, ${parentID}` : ''})"
                    />
                </div>
                <div class="createanother__checkbox">
                    <input type="checkbox" id="createAnother" name="createAnother">
                    <label for="createAnother">Create Another</label>
                </div>
            </div>`
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    createTask: async function (type, parentID = 0) {
        var taskprio = document.getElementById("taskprio").value
        if (type == 'task') parentID = document.getElementById("selectGroupID").value
        var tasktitle = document.getElementById("tasktitle").value
        var taskdescription = document.getElementById("taskdescription").value
        var createAnother = document.getElementById("createAnother").checked

        if (taskprio && tasktitle && taskdescription) {
            var url = `${DIR_SYSTEM}server/request.php?action=createTask`
            var formData = new FormData()
            formData.append('type', type)
            formData.append('taskprio', taskprio)
            formData.append('parentID', parentID)
            formData.append('tasktitle', tasktitle)
            formData.append('taskdescription', taskdescription)
            const response = await fetch(
                url, { method: 'POST', body: formData }
            )
            await response.json()
            if (createAnother) this.openCreateTaskForm(type, parentID, false)
            else closeDynamicForm()
            printSuccessToast('taskcreated')
        }
    },
    printSubtasks: async function (parentID) {
        const subtasks = await this.getSubtasks(parentID)
        const openSubtasks = subtasks.filter((task) => task.taskState == 'open')
        const closedSubtasks = subtasks.filter((task) => task.taskState == 'closed')
        var openTasksHTML = '', closedTasksHTML = ''
        console.log(subtasks)
        console.log(openSubtasks)
        if (openSubtasks) {
            openSubtasks.forEach(task => {
                openTasksHTML += this.printTask(task)
            })
            document.getElementById('subtask-open-header').innerHTML = `Open ${(openSubtasks) ? `(${openSubtasks.length})` : ''}`
            document.getElementById('subtasks-open-area').innerHTML = openTasksHTML
        }
        if (closedSubtasks) {
            closedSubtasks.forEach(task => {
                closedTasksHTML += this.printTask(task)
            })
            document.getElementById('subtask-closed-header').innerHTML = `Closed ${(closedSubtasks) ? `(${closedSubtasks.length})` : ''}`
            document.getElementById('subtasks-closed-area').innerHTML = closedTasksHTML
        }
    },
    printTask: function (task) {
        var subtaskLabelHTML = '', dayCounter = ''
        if (task.taskState == 'open') {
            if (task.subtaskCount > 1) subtaskLabelHTML = `<div class="label subtask_label">${task.subtaskCount} Subtasks</div>`
            else if (task.subtaskCount == 1) subtaskLabelHTML = `<div class="label subtask_label">1 Subtask</div>`
            dayCounter = `<div class="label bottom_label" ${(task.daysActive > 30) ? 'style="background-color:red;color:#fff;"' : ''}>${task.daysActive}</div>`
        }
        var html = ''
        html += `<a href="http://lukaslanger.bplaced.net/taskboard/php/details.php?action=taskDetails&id=${task.taskID}">
        <div class="box">
        <div class="priority" style="background-color: ${task.taskPriorityColor};"></div>
        <div class="content">
        <div class="text">${task.taskTitle}</div>
                    <div class="emptyspace"></div>
                    <div class="bottom">
                    <div class="label bottom_label">id_${task.taskID}</div>
                        ${(task.assigneeNameShort) ? `<div class="label bottom_label">${task.assigneeNameShort}</div>` : ''}
                        ${subtaskLabelHTML}
                        ${dayCounter}
                        </div>
                        </div>
                        </div>
                        </a>`
        return html
    },
    getSubtasks: async function (parentID) {
        var url = `${DIR_SYSTEM}server/request.php?action=getSubtasks`
        var formData = new FormData()
        formData.append('parentID', parentID)
        const response = await fetch(
            url, { method: 'POST', body: formData }
        )
        return await response.json()
    }
}