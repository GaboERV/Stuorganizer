document.addEventListener('DOMContentLoaded', () => {
    const taskList = document.getElementById('taskList');
    loadTasks();

    function loadTasks() {
        const tasks = getTasksFromStorage();
        taskList.innerHTML = '';

        tasks.forEach(task => {
            const taskItem = document.createElement('li');
            taskItem.innerHTML = `
                <div class="caja_tareas"><span><h3>${task.title}</h3> <p>Nota:<br>${task.description}</p> <p>Fecha:${task.dueDate}</p> <p/>Prioridad: ${task.priority}</p></span>
                ${task.file ? `<a href="${task.file.data}" download="${task.file.name}"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
  <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
  <path d="M7 11l5 5l5 -5" />
  <path d="M12 4l0 12" />
</svg></a></div>` : ''}
            `;
            taskList.appendChild(taskItem);
        });
    }

    function getTasksFromStorage() {
        return JSON.parse(localStorage.getItem('tasks')) || [];
    }
});
