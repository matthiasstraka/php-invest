const table = document.getElementById('datatable');

if (table) {
  table.addEventListener('click', e => {
    if (e.target.getAttribute('data-op') === "delete") {
      const name = e.target.getAttribute('data-name');
      if (confirm(`Do you really want to delete ${name}?`)) {
        const id = e.target.getAttribute('data-id')

        //console.log(`Deleting ${id}`);
        fetch(`${id}`, {method: 'DELETE'})
          .then(window.location.href = window.location.protocol +'//'+ window.location.host + window.location.pathname)
      }
    }
  });
}
