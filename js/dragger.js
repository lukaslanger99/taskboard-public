var draggables = document.querySelectorAll('.draggable__item')
const containers = document.querySelectorAll('.draggable__container')
var orderStart = []
var orderEnd = []

draggables.forEach(draggable => {
  draggable.addEventListener('dragstart', () => {
    draggable.classList.add('dragging__item')
    orderStart = []
    draggables.forEach(draggable => {
      orderStart.push(draggable.dataset.type)
    });
  })

  draggable.addEventListener('dragend', async () => {
    draggable.classList.remove('dragging__item')
    orderEnd = []
    draggables = document.querySelectorAll('.draggable__item')
    draggables.forEach(draggable => {
      orderEnd.push(draggable.dataset.type)
    });
    var orderChanged = false
    for (let i = 0; i < orderStart.length; i++) {
      if (orderStart[i] != orderEnd[i]) {
        orderChanged = true
      }
    }
    if (orderChanged) {
      var result = await updateOrder()
      console.log(result)
    }
  })
})

containers.forEach(container => {
  container.addEventListener('dragover', e => {
    e.preventDefault()
    const afterElement = getDragAfterElement(container, e.clientY)
    const draggable = document.querySelector('.dragging__item')
    if (afterElement == null) {
      container.appendChild(draggable)
    } else {
      container.insertBefore(draggable, afterElement)
    }
  })
})

function getDragAfterElement(container, y) {
  const draggableElements = [...container.querySelectorAll('.draggable__item:not(.dragging__item)')]

  return draggableElements.reduce((closest, child) => {
    const box = child.getBoundingClientRect()
    const offset = y - box.top - box.height / 2
    if (offset < 0 && offset > closest.offset) {
      return { offset: offset, element: child }
    } else {
      return closest
    }
  }, { offset: Number.NEGATIVE_INFINITY }).element
}

async function updateOrder() {
  var url = `${DIR_SYSTEM}server/request.php?action=updatePanelOrder`
  var formData = new FormData()
  formData.append('order', orderEnd)
  const response = await fetch(
      url, { method: 'POST', body: formData }
  )
  return await response.json()
}