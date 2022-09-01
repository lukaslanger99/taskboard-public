let labelHandler = {
  getLabels: async function (groupID) {
    var url = `${DIR_SYSTEM}server/request.php?action=getLabels`
    var formData = new FormData()
    formData.append('groupID', groupID)
    const response = await fetch(
      url, { method: 'POST', body: formData }
    )
    return await response.json()
  },
  getLabelsForTask: async function (groupID, taskID) {
    var url = `${DIR_SYSTEM}server/request.php?action=getLabelsForTask`
    var formData = new FormData()
    formData.append('groupID', groupID)
    formData.append('taskID', taskID)
    const response = await fetch(
      url, { method: 'POST', body: formData }
    )
    return await response.json()
  },
  openGroupLabelsPopup: async function (groupID) {
    const labels = await this.getLabels(groupID)
    var labelsHTML = ``
    if (labels) {
      var flag = false
      labels.forEach(label => {
        (flag) ? labelsHTML += `<hr class="solid">` : flag = true
        labelsHTML += `
        <div class="label__item draggable__item" draggable="true" data-type="${label.labelID}">
            <div class="label__left">
                <i class="fa fa-circle fa-2x" aria-hidden="true" style="color:${label.labelColor}"></i>
                <div>${label.labelName}</div>
            </div>
            <div class="label__right">
                <i 
                    class="fa fa-edit fa-2x" 
                    aria-hidden="true" 
                    onclick="labelHandler.updateLabelPopup(${groupID}, ${label.labelID}, '${label.labelName}', '${label.labelDescription}', '${label.labelColor}')"
                ></i>
                <i class="fa fa-trash fa-2x" aria-hidden="true" onclick="labelHandler.deleteLabel(${label.labelID}, ${groupID})"></i>
            </div>
        </div>`
      });
    }
    var container = document.getElementById("dynamic-modal-content")
    var html = `<div class="modal-header">
          <div class="modal__header__left">Labels</div>
          <div class="modal__header__right"><button onclick="labelHandler.createLabelPopup(${groupID})">New Label</button></div>
          <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>
        </div>
        <div class="label__list draggable__container">${labelsHTML}</div>`
    showDynamicForm(container, html)
    closeDynamicFormListener()
    addDraggableHelper('updateLabelOrder')
  },
  deleteLabel: async function (labelID, groupID) {
    var popup = confirm("Are you sure you want to delete this label?");
    if (popup == true) {
      var url = `${DIR_SYSTEM}server/request.php?action=deleteLabel`
      var formData = new FormData()
      formData.append('groupID', groupID)
      formData.append('labelID', labelID)
      await fetch(
        url, { method: 'POST', body: formData }
      )
      this.openGroupLabelsPopup(groupID)
    }
  },
  createLabelPopup: function (groupID) {
    var container = document.getElementById("dynamic-modal-content")
    var html = `<div class="modal-header">
          <div class="modal__header__left">New Label</div>
          <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>
        </div>
        <label htmlFor="title">Title</label><br>
        <input type="text" name="title" id="labeltitle-input"/><br>
        <label htmlFor="description">Description</label><br>
        <input type="text" name="description" id="labeldescription-input"/><br>
        <br>
        <label htmlFor="color">Color</label>
        <input type="color" name="color" id="labelcolor-input" value="#ff0000"/>
        <br>
        <input type="submit" value="Create" onclick="labelHandler.sendLabelData(${groupID})"/>`
    showDynamicForm(container, html)
    closeDynamicFormListener()
  },
  updateLabelPopup: function (groupID, labelID, title, description, color) {
    var container = document.getElementById("dynamic-modal-content")
    var html = `<div class="modal-header">
          <div class="modal__header__left">New Label</div>
          <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>
        </div>
        <label htmlFor="title">Title</label><br>
        <input type="text" name="title" id="labeltitle-input" value="${title}"/><br>
        <label htmlFor="description">Description</label><br>
        <input type="text" name="description" id="labeldescription-input" value="${description}"/><br>
        <br>
        <label htmlFor="color">Color</label>
        <input type="color" name="color" id="labelcolor-input" value="${color}"/>
        <br>
        <input type="submit" value="Update" onclick="labelHandler.sendLabelData(${groupID}, ${labelID})"/>`
    showDynamicForm(container, html)
    closeDynamicFormListener()
  },
  sendLabelData: async function (groupID, labelID = 0) {
    var title = document.getElementById("labeltitle-input").value
    var description = document.getElementById("labeldescription-input").value
    var color = document.getElementById("labelcolor-input").value
    if (title && description && color) {
      var url = `${DIR_SYSTEM}server/request.php?action=${(labelID) ? 'updateLabel' : 'createLabel'}`
      var formData = new FormData()
      formData.append('groupID', groupID)
      formData.append('title', title)
      formData.append('description', description)
      formData.append('color', color)
      if (labelID) formData.append('labelID', labelID)
      await fetch(
        url, { method: 'POST', body: formData }
      )
      this.openGroupLabelsPopup(groupID)
    }
  },
  editTaskLabels: async function (groupID, taskID) {
    const labels = await this.getLabelsForTask(groupID, taskID)
    var labelsHTML = ''
    if (labels) {
      labels.forEach(label => {
        labelsHTML += `
            <div>
                <input id="checkboxFor__${label.labelID}" type="checkbox" ${(label.isUsed) ? 'checked' : ''}/>
                <i class="fa fa-circle" aria-hidden="true" style="color: ${label.labelColor};"></i>
                <label>${label.labelName}</label>
            </div>`
      });
    }
    var container = document.getElementById("dynamic-modal-content")
    var html = `<div class="modal-header">
          <div class="modal__header__left">Labels</div>
          <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>
        </div>
        <div class="checkboxgroup">
            ${labelsHTML}
        </div>
        `
    showDynamicForm(container, html)
    closeDynamicFormListener()

    if (labels) {
      labels.forEach(label => {
        var checkbox = document.getElementById(`checkboxFor__${label.labelID}`)
        checkbox.addEventListener("click",
          async function () {
            var url = `${DIR_SYSTEM}server/request.php?action=updateTaskLabel`
            var formData = new FormData()
            formData.append('groupID', groupID)
            formData.append('taskID', taskID)
            formData.append('labelID', label.labelID)
            formData.append('checkboxChecked', checkbox.checked)
            await fetch(
              url, { method: 'POST', body: formData }
            )
            labelHandler.showLabelsInTaskDetails(groupID, taskID)
          })
      });
    }
  },
  showLabelsInTaskDetails: async function (groupID, taskID) {
    const usedLabels = (await this.getLabelsForTask(groupID, taskID)).filter((label) => label.isUsed)
    var labelHTML = ''
    if (usedLabels) {
      usedLabels.forEach(label => {
        labelHTML += `<div class="label" style="background-color: ${label.labelColor};">${label.labelName}</div>`
      });
    }
    labelHTML += `
      <i 
        class="fa fa-edit" 
        aria-hidden="true" 
        onclick="labelHandler.editTaskLabels(${groupID}, ${taskID})"
      ></i>`
    document.getElementById('tasklabel-list').innerHTML = `<div class="display-flex">${labelHTML}</div>`
  }
}
