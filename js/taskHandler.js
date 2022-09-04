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
                      <option value="2" selected>Normal</option>
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
        if (!(taskprio && tasktitle && taskdescription)) return printErrorToast("EMPTY_FIELDS")
        await requestHandler.sendRequest(
            'createTask', ['type', type], ['taskprio', taskprio], ['parentID', parentID], ['tasktitle', tasktitle], ['taskdescription', taskdescription])
        if (createAnother) this.openCreateTaskForm(type, parentID, false)
        else closeDynamicForm()
        printSuccessToast('TASK_CREATED')
        if (type == 'task') indexHandler.printIndexGroups()
        else taskdetailsHandler.printTaskdetails()
    },
    setTaskToOpen: async function (taskID) {
        await requestHandler.sendRequest('setTaskToOpen', ['taskID', taskID])
        printSuccessToast('TASK_OPEN')
        taskdetailsHandler.printTaskdetails()
    },
    assignTask: async function (taskID) {
        await requestHandler.sendRequest('assignTask', ['taskID', taskID])
        printSuccessToast('TASK_ASSIGNED')
        taskdetailsHandler.printTaskdetails()
    },
    resolveTask: async function (taskID) {
        const response = await requestHandler.sendRequest('resolveTask', ['taskID', taskID])
        if (response.ResponseCode != 'OK') return
        printSuccessToast('RESOLVED_TASK')
        taskdetailsHandler.printTaskdetails()
    },
    deleteTask: async function (taskID) {
        if (!confirm("Are you sure you want to delete Task id:" + taskID + "?")) return
        const response = await requestHandler.sendRequest('deleteTask', ['taskID', taskID])
        if (response.ResponseCode != 'OK') return
        location.href = response.data
    },
    createComment: async function (taskID) {
        const description = document.getElementById('commentDescription')
        if (!description) return printErrorToast("EMPTY_FIELDS")
        await requestHandler.sendRequest('createComment', ['taskID', taskID], ['description', description])
    },
    deleteComment: async function (commentID) {
        if (!confirm("Are you sure you want to delete this Comment?")) return
        await requestHandler.sendRequest('deleteComment', ['commentID', commentID])
    }
}