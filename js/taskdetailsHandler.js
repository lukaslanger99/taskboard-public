let taskdetailsHandler = {
    getTaskData: async function(taskID) {
        var url = `${DIR_SYSTEM}server/request.php?action=getTaskDataTaskdetails`
        var formData = new FormData()
        formData.append('taskID', taskID)
        const response = await fetch(
            url, { method: 'POST', body: formData }
        )
        return await response.json()
    },
    getPriorityColor: function (priority) {
        const priorities = ['green', '#ffcc00', 'red']
        return priorities[priority - 1]
    },
    printSubtasks: function (subtasks) {
        if (!subtasks) return ''
        html = ''
        subtasks.forEach(task => {
            html += `
                <div class="taskdetails__subtask">
                    <p><i class="fa fa-square" style="color: ${this.getPriorityColor(task.taskPriority)};"></i></p>
                    <p><a href="">ID_${task.taskID}"</a></p>
                    <p class="taskdetails__subtask__title">${task.taskTitle}</p>
                    <p>
                        ${(task.taskStatus == 'open') 
                        ? `<div class="status status__open">OPEN</div>`
                        : `<div class="status status__resolved">RESOLVED</div>`}
                    </p>
                </div>
                <hr>`
        })
        return html
    },
    printActivityComments: function (comments) {
        if (!comments) return ''
        const activityComments = comments.filter((entry) => entry.type == 'comment')
        if (!activityComments) return ''
        html = ``
        activityComments.forEach(comment => {
            html += `
                <div class="activity__comment">
                    <p class="comment__header">${comment.commentAuthor} added a comment - ${comment.commentDateFormatted}</p>
                    <div class="comment__content">
                        ${comment.commentDescription}
                    </div>
                </div>
                <hr>`
        })
        return html
    },
    printTaskdetails: async function () {
        const taskData = await this.getTaskData(document.URL.replace(/.*id=([^&]*).*|(.*)/, '$1'))
        _parentsListHTML = ``
        taskData.parents.forEach(item => {
            if (item.type == 'group') _parentsListHTML += `<li><a href="${DIR_SYSTEM}php/details.php?action=groupDetails&id=${item.id}">${item.name}</a></li>`
            else _parentsListHTML += `<li><a href="${DIR_SYSTEM}php/details.php?action=taskDetails&id=${item.id}">ID-${item.id}</a></li>`
        });
        headerHTML = `
            <div class="taskdetails__header">
                <div class="taskdetails__header__image">
                    <img alt="" src="">
                </div>
                <div class="taskdetails__header__main">
                    <ul class="breadcrumb">
                        ${_parentsListHTML}
                    </ul>
                    <h1 class="taskdetails__title">${taskData.taskTitle}</h1>
                </div>
            </div>`
        buttonsHTML = `
            <div class="taskdetails__buttons">
                <button onclick="openUpdateTaskForm()">Update</button>
                <button onclick="taskHandler.deleteTask(${taskData.taskID}, '${taskData.taskType}')">Delete</button>
                <button onclick="taskHandler.openCreateTaskForm('${taskData.taskType}', ${taskData.taskID}, 'false')">Create Subtask</button>
                <button onclick="taskHandler.assignTask(${taskData.taskID})">Assign Task</button>
                <button onclick="taskHandler.resolveTask(${taskData.taskID})">Resolve</button>
            </div>`
        __detailsModuleHTML = `
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button
                        type="button"
                        class="toggle__wrap" 
                        id="taskdetailsModuleButton_details" 
                        onclick="taskdetailsHandler.taskdetailsToggleContent('taskdetailsModuleButton_details', 'taskdetailsModuleContent_details')"
                    >
                        <i class="fa fa-angle-down"></i>
                    </button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">Details</div>
                    <div class="taskdetails__module__content" id="taskdetailsModuleContent_details">
                        <table class="taskdetails__datatable">
                            <tr>
                                <td>Status:</td>
                                <td>
                                    ${(taskData.taskStatus == 'open') 
                                    ? `<div class="status status__open">OPEN</div>` 
                                    : `<div class="status status__resolved">RESOLVED</div>`}
                                </td>
                            </tr>
                            <tr>
                                <td>Priority:</td>
                                <td>
                                    <select name="priority" id="taskprio">
                                        <option ${(taskData.taskPriority == 1) ? `selected="selected"` : ``} value="1">Low</option>
                                        <option ${(taskData.taskPriority == 2) ? `selected="selected"` : ``} value="2">Normal</option>
                                        <option ${(taskData.taskPriority == 3) ? `selected="selected"` : ``} value="3">High</option>
                                    </select>
                                </td>
                            </tr>
                            ${(taskData.taskType == 'task') ? `<tr><td>Labels:</td><td id="tasklabel-list"></td></tr>` : ``}
                        </table>
                    </div>
                </div>
            </div>`
        __descriptionModuleHTML = `
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button
                        type="button"
                        class="toggle__wrap" 
                        id="taskdetailsModuleButton_description" 
                        onclick="taskdetailsHandler.taskdetailsToggleContent('taskdetailsModuleButton_description', 'taskdetailsModuleContent_description')"
                    >
                        <i class="fa fa-angle-down"></i>
                    </button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">Description</div>
                    <div class="taskdetails__module__content" id="taskdetailsModuleContent_description">
                        ${taskData.taskDescription}
                    </div>
                </div>
            </div>`
        __subtasksModuleHTML = `
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button
                        type="button"
                        class="toggle__wrap" 
                        id="taskdetailsModuleButton_subtasks" 
                        onclick="taskdetailsHandler.taskdetailsToggleContent('taskdetailsModuleButton_subtasks', 'taskdetailsModuleContent_subtasks')"
                    >
                        <i class="fa fa-angle-down"></i>
                    </button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">Subtasks</div>
                    <div class="taskdetails__module__content" id="taskdetailsModuleContent_subtasks">
                        ${this.printSubtasks(taskData.subtasks)}
                    </div>
                </div>
            </div>`
        __activityModuleHTML = `
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button
                        type="button"
                        class="toggle__wrap" 
                        id="taskdetailsModuleButton_activity" 
                        onclick="taskdetailsHandler.taskdetailsToggleContent('taskdetailsModuleButton_activity', 'taskdetailsModuleContent_activity')"
                    >
                        <i class="fa fa-angle-down"></i>
                    </button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">Activity</div>
                    <div class="taskdetails__module__content" id="taskdetailsModuleContent_activity">
                        <div class="activity__header">
                            <p class="activity__item"id="activity_all">All</p>
                            <p class="activity__item activity__active" id="activity_comments">Comments</p>
                            <p class="activity__item"id="activity_history">History</p>
                        </div>
                        <hr>
                        ${this.printActivityComments(taskData.activity)}
                    </div>
                </div>
            </div>`
        _bigModulesHTML = `
            <div class="taskdetails__big__modules">
                ${__detailsModuleHTML}${__descriptionModuleHTML}${__subtasksModuleHTML}${__activityModuleHTML}
            </div>`
        __peopleModuleHTML = `
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button
                        type="button"
                        class="toggle__wrap" 
                        id="taskdetailsModuleButton_people" 
                        onclick="taskdetailsHandler.taskdetailsToggleContent('taskdetailsModuleButton_people', 'taskdetailsModuleContent_people')"
                    >
                        <i class="fa fa-angle-down"></i>
                    </button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">People</div>
                    <div class="taskdetails__module__content" id="taskdetailsModuleContent_people">
                        <table class="taskdetails__datatable">
                            <tr>
                                <td>Assignee:</td>
                                <td>${taskData.assignee}</td>
                            </tr>
                            <tr>
                                <td>Reporter:</td>
                                <td>${taskData.reporter}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>`
        __datesModuleHTML = `
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                <button
                type="button"
                class="toggle__wrap" 
                id="taskdetailsModuleButton_dates" 
                onclick="taskdetailsHandler.taskdetailsToggleContent('taskdetailsModuleButton_dates', 'taskdetailsModuleContent_dates')"
            >
                <i class="fa fa-angle-down"></i>
            </button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">Dates</div>
                    <div class="taskdetails__module__content" id="taskdetailsModuleContent_dates">
                        <table class="taskdetails__datatable">
                            <tr>
                                <td>Created:</td>
                                <td>${taskData.dateCreatedFormatted}</td>
                            </tr>
                            <tr>
                                <td>Updated:</td>
                                <td>${taskData.dateUpdatedFormatted}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                </div>
            </div>`
        _smallModulesHTML = `
            <div class="taskdetails__small__modules">
                ${__peopleModuleHTML}${__datesModuleHTML}
            </div>`
        modulesHTML = `
            <div class="taskdetails__modules">
                ${_bigModulesHTML}${_smallModulesHTML}
            </div>`
    document.getElementById('taskdetails').innerHTML = `${headerHTML}${buttonsHTML}${modulesHTML}`
    if (taskData.taskType == 'task') labelHandler.showLabelsInTaskDetails(taskData.taskParentID, taskData.taskID)
    },
    taskdetailsToggleContent: function (buttonID, contentAreaID) {
        var button = document.getElementById(buttonID)
        var container = document.getElementById(contentAreaID)
        if (button && container) {
          var containerDisplay = getComputedStyle(container).display;
          if (containerDisplay == 'none') {
            container.style.display = 'block'
            button.innerHTML = `<i class="fa fa-angle-down"></i>`
          } else if (containerDisplay == 'block') {
            container.style.display = 'none'
            button.innerHTML = `<i class="fa fa-angle-right"></i>`
          }
        }
    }
}