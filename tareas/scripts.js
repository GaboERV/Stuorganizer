// Función para mostrar el formulario de actualización de tareas
function showUpdateForm(taskId) {
    fetch('/Proyecto/tareas/get_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: taskId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const task = data.task;
            const formHtml = `
                <form id="update-task-form">
                    <input type="hidden" name="id" value="${task.id}">
                    <label for="title">Título:</label>
                    <input type="text" name="title" id="title" value="${task.title}" required>
                    <label for="description">Descripción:</label>
                    <textarea name="description" id="description" required>${task.description}</textarea>
                    <label for="due_date">Fecha de vencimiento:</label>
                    <input type="date" name="due_date" id="due_date" value="${task.due_date}" required>
                    <label for="priority">Prioridad:</label>
                    <select name="priority" id="priority" required>
                        <option value="Alta" ${task.priority === 'Alta' ? 'selected' : ''}>Alta</option>
                        <option value="Media" ${task.priority === 'Media' ? 'selected' : ''}>Media</option>
                        <option value="Baja" ${task.priority === 'Baja' ? 'selected' : ''}>Baja</option>
                    </select>
                    <button type="button" onclick="updateTask(${task.id})">Actualizar Tarea</button>
                </form>
            `;
            document.getElementById('task-form-container').innerHTML = formHtml;
        } else {
            alert('Error al obtener la tarea.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Función para actualizar una tarea
function updateTask(taskId) {
    const form = document.getElementById('update-task-form');
    const formData = new FormData(form);
    const taskData = Object.fromEntries(formData.entries());

    fetch('/Proyecto/tareas/update_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(taskData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tarea actualizada con éxito.');
            location.reload();
        } else {
            alert('Error al actualizar la tarea: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la tarea: ' + error);
    });
}


// Función para eliminar una tarea

function deleteTask(taskId) {
    if (confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
        fetch('/Proyecto/tareas/tareas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: taskId })
        })
       .then(response => response.json())
       .then(data => {
            console.log('Respuesta del servidor:', data);
            if (data.success) {
                alert('Tarea eliminada con éxito.');
                location.reload();
            } else {
                alert('Error al eliminar la tarea: ' data.message);
                console.error('Error:', data.message);
            }
        })
       .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la tarea: ' error);
        });
    }
}
// Función para enviar una tarea por correo electrónico
function emailTask(taskId) {
    fetch('/Proyecto/tareas/get_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: taskId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const task = data.task;
            const toEmail = prompt('Introduce el correo electrónico del destinatario:');

            if (toEmail) {
                const emailParams = {
                    to_email: toEmail,
                    task_title: task.title,
                    task_description: task.description,
                    task_due_date: task.due_date,
                    task_priority: task.priority
                };

                emailjs.send('service_jpfnqbp', 'template_fi27z0h', emailParams)
                    .then((response) => {
                        alert('Correo enviado con éxito.');
                    }, (error) => {
                        alert('Error al enviar el correo.');
                        console.error('Error:', error);
                    });
            } else {
                alert('Correo electrónico no válido.');
            }
        } else {
            alert('Error al obtener la tarea.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
