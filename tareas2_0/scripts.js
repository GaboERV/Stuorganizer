
// Función para mostrar mensajes de alerta estilizados
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Función para validar el formulario antes de enviarlo
function validateForm(form) {
    const title = form.title.value.trim();
    const description = form.description.value.trim();
    const dueDate = new Date(form.due_date.value);
    const today = new Date();

    if (title.length < 3) {
        showAlert('El título debe tener al menos 3 caracteres', 'error');
        return false;
    }

    if (description.length < 10) {
        showAlert('La descripción debe tener al menos 10 caracteres', 'error');
        return false;
    }

    if (dueDate < today) {
        showAlert('La fecha de vencimiento no puede ser en el pasado', 'error');
        return false;
    }

    return true;
}

// Añadir validación a los formularios
document.getElementById('taskForm').addEventListener('submit', function(e) {
    if (!validateForm(this)) {
        e.preventDefault();
    }
});

// Función para ordenar las tareas
function sortTasks(criteria) {
    const taskList = document.getElementById('taskList');
    const tasks = Array.from(taskList.children);

    tasks.sort((a, b) => {
        let aValue = a.querySelector(`.${criteria}`).textContent;
        let bValue = b.querySelector(`.${criteria}`).textContent;

        if (criteria === 'due_date') {
            return new Date(aValue) - new Date(bValue);
        } else if (criteria === 'priority') {
            const priorityOrder = { 'Alta': 1, 'Media': 2, 'Baja': 3 };
            return priorityOrder[aValue] - priorityOrder[bValue];
        } else {
            return aValue.localeCompare(bValue);
        }
    });

    taskList.innerHTML = '';
    tasks.forEach(task => taskList.appendChild(task));
}

// Añadir botones de ordenación
const sortButtons = `
    <div class="sort-buttons">
        <button onclick="sortTasks('title')">Ordenar por Título</button>
        <button onclick="sortTasks('due_date')">Ordenar por Fecha</button>
        <button onclick="sortTasks('priority')">Ordenar por Prioridad</button>
    </div>
`;
document.querySelector('.task-list-container').insertAdjacentHTML('afterbegin', sortButtons);