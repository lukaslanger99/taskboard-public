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
    printTaskdetails: async function () {
        const taskData = await this.getTaskData(document.URL.replace(/.*id=([^&]*).*|(.*)/, '$1'))
        _parentsListHTML = ``
        taskData.parents.forEach(item => {
            if (item.type == 'group') _parentsListHTML += `<li><a href="${DIR_SYSTEM}php/details.php?action=taskDetails&id=${item.id}">${item.name}</a></li>`
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
                                    ${(taskData.taskState == 'open') 
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
                            <tr>
                                <td>Labels:</td>
                                <td>
                                    ${TODO print labels}
                                    <div class="display-flex">
                                        <div class="label" style="background-color: #0091ff;">in progress</div>
                                        <i class="fa fa-edit" aria-hidden="true" onclick="labelHandler.editTaskLabels(6, 456)"></i>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>`
        __descriptionModuleHTML = ``
        __subtasksModuleHTML = ``
        __activityModuleHTML = ``
        _bigModulesHTML = `
            <div class="taskdetails__big__modules">
                ${__detailsModuleHTML}${__descriptionModuleHTML}${__subtasksModuleHTML}${__activityModuleHTML}
            </div>`
        __peopleModuleHTML = ``
        __datesModuleHTML = ``
        _smallModulesHTML = `
            <div class="taskdetails__small__modules">
                ${__peopleModuleHTML}${__datesModuleHTML}
            </div>`
        modulesHTML = `
            <div class="taskdetails__modules">
                ${_bigModulesHTML}${_smallModulesHTML}
            </div>`
            <!--Details-->

            <!--Description-->
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button class="toggle__wrap"><i class="fa fa-angle-down"></i></button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">Description</div>
                    <div class="taskdetails__module__content" id="modulename_module_content">
                        This is a description hello there <br>
                        <p>test test test test test test</p>
                        pümpel officer of the month <br><br>
                        laggggggsagg
                    </div>
                </div>
            </div>
            <!--Subtasks-->
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button class="toggle__wrap"><i class="fa fa-angle-down"></i></button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">Subtasks</div>
                    <div class="taskdetails__module__content" id="modulename_module_content">
                        <div class="taskdetails__subtask">
                            <p><i class="fa fa-square" style="color: #ffcc00;"></i></p>
                            <p><a href="">ID_629</a></p>
                            <p class="taskdetails__subtask__title">in description so makros abbilden dass striche am anfang liste ergeben usw.</p>
                            <p><div class="status status__open">OPEN</div></p>
                        </div>
                        <hr>
                        <div class="taskdetails__subtask">
                            <p><i class="fa fa-square" style="color: #ffcc00;"></i></p>
                            <p><a href="">ID_934</a></p>
                            <p class="taskdetails__subtask__title">update task with inputs</p>
                            <p><div class="status status__open">OPEN</div></p>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
            <!--Activity-->
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button class="toggle__wrap"><i class="fa fa-angle-down"></i></button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">Activity</div>
                    <div class="taskdetails__module__content" id="modulename_module_content">
                        <div class="activity__header">
                            <p class="activity__item"id="activity_all">All</p>
                            <p class="activity__item activity__active" id="activity_comments">Comments</p>
                            <p class="activity__item"id="activity_history">History</p>
                        </div>
                        <hr>
                        <div class="activity__comment">
                            <p class="comment__header">lukaslanger99 added a comment - 14/Feb/2022 01:56</p>
                            <div class="comment__content">
                                Jira style
                            </div>
                        </div>
                        <hr>
                        <div class="activity__comment">
                            <p class="comment__header">lukaslanger99 added a comment - 15/Feb/2022 12:02</p>
                            <div class="comment__content">
                                Links scrollbar mit tasks asiigned und assigned label, dann open label und alle offenen die man annehmen kann. Links dann jira style details
                            </div>
                        </div>
                        <hr>
                        <div class="activity__comment">
                            <p class="comment__header">lukaslanger99 added a comment - 24/Feb/2022 11:52</p>
                            <div class="comment__content">
                                https://gyazo.com/a72619d037ea03e43f9afbf21fbf0b2b
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
        <div class="taskdetails__small__modules">
            <!--People-->
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button class="toggle__wrap"><i class="fa fa-angle-down"></i></button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">People</div>
                    <div class="taskdetails__module__content" id="modulename_module_content">
                        <table class="taskdetails__datatable">
                            <tr>
                                <td>Assignee:</td>
                                <td>lukaslanger99</td>
                            </tr>
                            <tr>
                                <td>Reporter:</td>
                                <td>Unknown</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <!--Dates-->
            <div class="taskdetails__module">
                <div class="taskdetails__module__left">
                    <button class="toggle__wrap"><i class="fa fa-angle-down"></i></button>
                </div>
                <div class="taskdetails__module__right">
                    <div class="header__title">Dates</div>
                    <div class="taskdetails__module__content" id="modulename_module_content">
                        <table class="taskdetails__datatable">
                            <tr>
                                <td>Created:</td>
                                <td>31/Dec/2020 02:56</td>
                            </tr>
                            <tr>
                                <td>Updated:</td>
                                <td>2 days ago</td>
                            </tr>
                        </table>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
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