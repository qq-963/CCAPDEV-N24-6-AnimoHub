// admin page, organizations part
// adding/editing/deleting is done by php forms now (see AdminOrgController).
// the only thing left for javascript is the "are you sure?" popup before deleting.
document.addEventListener("DOMContentLoaded", function () {
  var forms = document.querySelectorAll(".deleteForm");

  for (var i = 0; i < forms.length; i++) {
    forms[i].onsubmit = function () {
      // data-name on the form has the org name
      var name = this.getAttribute("data-name");
      // returning false cancels the submit, so nothing gets deleted
      return confirm(
        'Delete "' +
          name +
          '"? Its members, events and announcements will be deleted too. This cant be undone.',
      );
    };
  }
});
