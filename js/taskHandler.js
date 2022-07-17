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
    }
}