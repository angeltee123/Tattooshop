// editing reservations
var edit_reservations = document.getElementById('edit_reservations');
var edit_reservations_label = document.getElementById('edit_reservations_label');

// reservations fields
var update_buttons = document.getElementsByName('update_reservation');
var reservation_row_fields = document.querySelectorAll(".reservations.form-control");
var reservation_selects = document.querySelectorAll(".reservations.form-select");

// reservations item fields
var reservation_forms = Array.from(document.getElementsByClassName('Reservations__item__form'));
var reservation_status = Array.from(document.getElementsByClassName('Reservation__item__status'));
var reservation_time = Array.from(document.getElementsByName('scheduled_time'));
var reservation_date = Array.from(document.getElementsByName('scheduled_date'));
var reservation_address = Array.from(document.getElementsByName('reservation_address'));

// reservations item fields feedback
var reservation_time_err = Array.from(document.getElementsByClassName('scheduled_time_err'));
var reservation_date_err = Array.from(document.getElementsByClassName('scheduled_date_err'));
var reservation_address_err = Array.from(document.getElementsByClassName('reservation_address_err'));

// reservation item submit buttons
var update_reservation = Array.from(document.getElementsByName('update_reservation'));
var confirm_reservation = Array.from(document.getElementsByName('confirm_reservation'));

// collapsible toggling
var show_reservations = false;
var toggle_reservations = document.getElementById('toggle_reservations');

// collapsibles
var reservations = document.getElementsByClassName('Reservations__item');
var reservations_collapsibles = document.getElementsByClassName('Reservations__item__collapsible__body');

// collapsibles stateful styling
for(var i=0, count=reservations.length; i < count; i++){
  reservations[i].addEventListener('shown.bs.collapse', function (){
    this.classList.add('mb-5');
  });

  reservations[i].addEventListener('hidden.bs.collapse', function (){
    this.classList.remove('mb-5');
  });
}

// toggle collapsibles
toggle_reservations.addEventListener('click', function(){
  show_reservations = !show_reservations;
  show_reservations === true ? toggle_reservations.innerText = "Hide All Reservations" : toggle_reservations.innerText = "Show All Reservations";
  
  for(var i=0, count=reservations_collapsibles.length; i < count; i++){
    if(show_reservations === true){
      if(!(reservations_collapsibles[i].classList.contains('show'))){
        let collapse = new bootstrap.Collapse(reservations_collapsibles[i], { show: true, hide: false });
      }
    } else {
      if((reservations_collapsibles[i].classList.contains('show'))){
        let collapse = new bootstrap.Collapse(reservations_collapsibles[i], { show: false, hide: true });
      }
    }
  }
});

// toggling reservation editing
edit_reservations.addEventListener('click', function(){
    this.checked ? edit_reservations_label.innerText = "Stop Editing" : edit_reservations_label.innerText = "Edit";
    
    for(var i=0, count=reservation_row_fields.length; i < count; i++){
      reservation_row_fields[i].readOnly = this.checked ? false : true;
    }

    for(var i=0, count=reservation_selects.length; i < count; i++){
      if(this.checked){
        reservation_selects[i].classList.remove('no-select');
        reservation_selects[i].classList.remove('form-select-plaintext');

        for(var j = 0; j < reservation_selects[i].options.length; j++){
          reservation_selects[i].options[j].disabled = false;
        }
      } else {
        reservation_selects[i].classList.add('no-select');
        reservation_selects[i].classList.add('form-select-plaintext');

        for(var j = 0; j < reservation_selects[i].options.length; j++){
          if(!reservation_selects[i].options[j].selected){
            reservation_selects[i].options[j].disabled = true;
          }
        }
      }
    }

    for(var j=0, count=update_buttons.length; j < count; j++){
      this.checked ? update_buttons[j].classList.replace('d-none', 'd-flex') : update_buttons[j].classList.replace('d-flex', 'd-none');
    }
});

// error reporting
for(let x = 0, count = reservation_forms.length; x < count; x++){
  reservation_forms[x].addEventListener('change', function(){
    index = reservation_forms.indexOf(this);
    if(reservation_status[index].innerText.toString().trim().localeCompare("Pending") == 0){
      let errors = [];

      let field = reservation_time[index];
      let hours = parseInt(field.value.substring(0, 2));
      let minutes = parseInt(field.value.split(":").pop());
      let message = reservation_time_err[index].lastChild;
      if(numbers.test(field.value) == false || time.test(field.value) == false || hours < 0 || hours > 24 || minutes < 0 || minutes > 60){
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');

        message.innerText = "Valid time format required.";

        reservation_time_err[index].classList.replace('d-none', 'd-flex');
        errors.push(message.innerText || message.innerHTML);
      } else {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        reservation_time_err[index].classList.replace('d-flex', 'd-none');
      }

      field = reservation_date[index];
      message = reservation_date_err[index].lastChild;
      let date = field.value;
      if (isNaN(Date.parse(date)) == true || new Date(date) <= today) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        
        message.innerText = (isNaN(Date.parse(date)) == true) ? "Valid date required." : "Date should not be current or past dates.";

        reservation_date_err[index].classList.replace('d-none', 'd-flex');
        errors.push(message.innerText || message.innerHTML);
      } else {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        reservation_date_err[index].classList.replace('d-flex', 'd-none');
      }

      field = reservation_address[index];
      message = reservation_address_err[index].lastChild;
      if (field.value.toString().trim().length == 0) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        
        message.innerText = "Address is required.";

        reservation_address_err[index].classList.replace('d-none', 'd-flex');
        errors.push(message.innerText || message.innerHTML);
      } else {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        reservation_address_err[index].classList.replace('d-flex', 'd-none');
      }

      // submission guard
      if(errors.length > 0){
        update_reservation[index].disabled = true;
        confirm_reservation[index].disabled = true;
      } else {
        update_reservation[index].disabled = false;
        confirm_reservation[index].disabled = false;
      }
    }
  });
}