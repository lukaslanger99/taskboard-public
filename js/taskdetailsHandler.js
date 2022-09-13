let taskdetailsHandler = {
    activities: [],
    getTaskDataTaskdetails: async function (taskID) {
        const response = await requestHandler.sendRequest('getTaskDataTaskdetails', ['taskID', taskID])
        return response.data
    },
    getPriorityColor: function (priority) {
        const priorities = ['green', '#ffcc00', 'red']
        return priorities[priority - 1]
    },
    printSubtasks: function (subtasks) {
        if (subtasks.ResponseCode == 'NO_SUBTASKS') return ''
        html = ''
        subtasks.forEach(task => {
            html += `
                <div class="taskdetails__subtask">
                    <p><i class="fa fa-square" style="color: ${this.getPriorityColor(task.taskPriority)};"></i></p>
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
        return html
    },
    printActivityComments: function (filterKeyword) {
        if (!this.activities) return ''
        var comments = []
        if (filterKeyword == 'all') comments = this.activities
        else comments = this.activities.filter((entry) => entry.commentType == filterKeyword)
        if (!comments) return ''
        html = ``
        comments.forEach(comment => {
            html += `
                <div class="activity__comment">
                    <p class="comment__header">
                        ${comment.commentAuthor} ${(comment.commentType == 'comment') ? 'added a comment' : 'updated task'} - ${comment.commentDateFormatted}
                    </p>
                    <div class="comment__content">
                        ${comment.descriptionWithMakros}
                    </div>
                </div>
                <hr>`
        })
        document.getElementById('activity_all').classList.remove('activity__active')
        document.getElementById('activity_comments').classList.remove('activity__active')
        document.getElementById('activity_history').classList.remove('activity__active')
        if (filterKeyword == 'all') document.getElementById('activity_all').classList.add('activity__active')
        else if (filterKeyword == 'comment') document.getElementById('activity_comments').classList.add('activity__active')
        else if (filterKeyword == 'history') document.getElementById('activity_history').classList.add('activity__active')
        return html
    },
    printHeader: function (parentListHTML, title) {
        return `
            <div class="taskdetails__header">
                <div class="taskdetails__header__image">
                    <img alt="" src="">
                </div>
                <div class="taskdetails__header__main">
                    <ul class="breadcrumb">
                        ${parentListHTML}
                    </ul>
                    <h1 class="taskdetails__title">${title}</h1>
                </div>
            </div>`
    },
    printButtons: function (taskID, type, status) {
        return `
            <div class="taskdetails__buttons">
                <button onclick="taskHandler.openUpdateTaskForm(${taskID})">Update</button>
                <button onclick="taskHandler.deleteTask(${taskID}, '${type}')">Delete</button>
                <button onclick="taskHandler.openCreateTaskForm('subtask', ${taskID}, 'false')">Create Subtask</button>
                <button onclick="taskHandler.assignTask(${taskID})">Assign Task</button>
                ${(status == 'open')
                ? `<button onclick="taskHandler.resolveTask(${taskID})">Resolve</button>`
                : `<button onclick="taskHandler.setTaskToOpen(${taskID})">Back to Open</button>`}
            </div>`
    },
    printDetailsModule: function (status, priority, type) {
        const priorities = ['Low', 'Normal', 'High']
        return `
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
                                <td class="letf__td">Status:</td>
                                <td>
                                    ${(status == 'open')
                ? `<div class="status status__open">OPEN</div>`
                : `<div class="status status__resolved">RESOLVED</div>`}
                                </td>
                            </tr>
                            <tr>
                                <td class="letf__td">Priority:</td>
                                <td>${priorities[priority - 1]}</td>
                            </tr>
                            ${(type == 'task') ? `<tr><td class="letf__td">Labels:</td><td id="tasklabel-list"></td></tr>` : ``}
                        </table>
                    </div>
                </div>
            </div>`
    },
    printDescriptionModule: function (description) {
        return `
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
                        ${description}
                    </div>
                </div>
            </div>`
    },
    printSubtasksModule: function (subtasks) {
        return `
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
                        ${this.printSubtasks(subtasks)}
                    </div>
                </div>
            </div>`
    },
    printActivityModule: function (taskID) {
        return `
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
                            <p class="activity__item" id="activity_all" onclick="taskdetailsHandler.addActivitiesToModule('all')">All</p>
                            <p class="activity__item" id="activity_comments" onclick="taskdetailsHandler.addActivitiesToModule('comment')">Comments</p>
                            <p class="activity__item" id="activity_history" onclick="taskdetailsHandler.addActivitiesToModule('history')">History</p>
                        </div>
                        <hr>
                        <div id="taskdetails-activities"></div>
                    </div>
                    <button onclick="taskHandler.openCreateCommentPopup(${taskID})">
                        <i class="fa fa-comment"></i>
                        Add Comment
                    </button>
                </div>
            </div>`
    },
    addActivitiesToModule: function (filterKeyword) {
        var html = this.printActivityComments(filterKeyword)
        document.getElementById('taskdetails-activities').innerHTML = html
    },
    printPeopleModule: function (assignee, reporter) {
        return `
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
                                <td class="letf__td">Assignee:</td>
                                <td>${assignee}</td>
                            </tr>
                            <tr>
                                <td class="letf__td">Reporter:</td>
                                <td>${reporter}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>`
    },
    printDatesModule: function (status, dates) {
        return `
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
                                <td class="letf__td">Created:</td>
                                <td>${dates.dateCreatedFormatted}</td>
                            </tr>
                            <tr>
                                <td class="letf__td">Updated:</td>
                                <td>${dates.dateUpdatedFormatted}</td>
                            </tr>
                            ${(status != 'open')
                ? `<tr><td class="letf__td">Resolved:</td><td>${dates.dateResolvedFormatted}</td></tr>`
                : ``}
                        </table>
                    </div>
                </div>
                </div>
            </div>`
    },
    printTaskdetails: async function () {
        const taskData = await this.getTaskDataTaskdetails(document.URL.replace(/.*id=([^&]*).*|(.*)/, '$1'))
        _parentsListHTML = ``
        taskData.parents.forEach(item => {
            if (item.type == 'group') _parentsListHTML += `<li><a href="${DIR_SYSTEM}php/details.php?action=groupDetails&id=${item.id}">${item.name}</a></li>`
            else _parentsListHTML += `<li><a href="${DIR_SYSTEM}php/details.php?action=taskDetails&id=${item.id}">ID-${item.id}</a></li>`
        });
        headerHTML = this.printHeader(_parentsListHTML, taskData.taskTitle)
        buttonsHTML = this.printButtons(taskData.taskID, taskData.taskType, taskData.taskStatus)
        __detailsModuleHTML = this.printDetailsModule(taskData.taskStatus, taskData.taskPriority, taskData.taskType)
        __descriptionModuleHTML = this.printDescriptionModule(taskData.descriptionWithMakros)
        __subtasksModuleHTML = this.printSubtasksModule(taskData.subtasks)
        __activityModuleHTML = this.printActivityModule(taskData.taskID, taskData.activity)
        _bigModulesHTML = `
            <div class="taskdetails__big__modules">
                ${__detailsModuleHTML}${__descriptionModuleHTML}${__subtasksModuleHTML}${__activityModuleHTML}
            </div>`
        __peopleModuleHTML = this.printPeopleModule(taskData.assignee, taskData.reporter)
        __datesModuleHTML = this.printDatesModule(taskData.taskStatus, taskData.datesFormatted)
        _smallModulesHTML = `
            <div class="taskdetails__small__modules">
                ${__peopleModuleHTML}${__datesModuleHTML}
            </div>`
        modulesHTML = `
            <div class="taskdetails__modules">
                ${_bigModulesHTML}${_smallModulesHTML}
            </div>`
        document.getElementById('taskdetails').innerHTML = `${headerHTML}${buttonsHTML}${modulesHTML}`
        this.activities = taskData.activity
        this.addActivitiesToModule('comment')
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