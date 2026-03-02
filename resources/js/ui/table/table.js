$(document).ready(function() {
  $('.main_table').DataTable({
      "language": {
          url: "https://cdn.datatables.net/plug-ins/1.11.5/i18n/ja.json",
      },
      lengthChange: false,
      info: false,
      order : [[ 0, "desc" ]],
  });
});
