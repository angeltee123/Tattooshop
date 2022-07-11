// reservations fields
var update_buttons = document.getElementsByName('update_reservation');
var reservation_row_fields = document.querySelectorAll(".reservations.form-control");
var reservation_selects = document.querySelectorAll(".reservations.form-select");

// reservations item fields
var reservation_forms = Array.from(document.getElementsByClassName('Reservations__item__form'));
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
var show_all_items = false;
var toggle_items = document.getElementById('toggle_items');

// collapsibles
var items = document.getElementsByClassName('Reservations__item');
var item_collapsibles = document.getElementsByClassName('Reservations__item__collapsible__body');

// collapsibles stateful styling
for(var i=0, count=items.length; i < count; i++){
  items[i].addEventListener('shown.bs.collapse', function (){
    this.classList.add('mb-5');
  });

  items[i].addEventListener('hidden.bs.collapse', function (){
    this.classList.remove('mb-5');
  });
}

// toggling all collapsibles
toggle_items.addEventListener('click', function(){
  show_all_items = !show_all_items;
  show_all_items === true ? toggle_items.innerText = "Hide All" : toggle_items.innerText = "Show All";
  
  for(var i=0, count=item_collapsibles.length; i < count; i++){
    if(show_all_items === true){
      if(!(item_collapsibles[i].classList.contains('show'))){
        let collapse = new bootstrap.Collapse(item_collapsibles[i], { show: true, hide: false });
      }
    } else {
      if((item_collapsibles[i].classList.contains('show'))){
        let collapse = new bootstrap.Collapse(item_collapsibles[i], { show: false, hide: true });
      }
    }
  }
});

// error reporting
for(let x = 0, count = reservation_forms.length; x < count; x++){
  reservation_forms[x].addEventListener('change', function(){
    index = reservation_forms.indexOf(this);
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
    (errors.length > 0) ? update_reservation[index].disabled = true : update_reservation[index].disabled = false;
  });
}