// search bar
var search = document.getElementById('search');

// catalogue
var catalogue = document.getElementById('Catalogue');
var cards = document.getElementsByClassName('Catalogue__cards__card');

// modal forms
var modal_forms = Array.from(document.getElementsByClassName('Catalogue__cards__modal__form'));

// modal form fields
var quantity_fields = Array.from(document.getElementsByName('quantity'));
var width_fields = Array.from(document.getElementsByName('width'));
var height_fields = Array.from(document.getElementsByName('height'));
var submit_btns = Array.from(document.getElementsByName('order_item'));

// modal form fields feedback
var quantity_err = Array.from(document.getElementsByClassName('quantity_err'));
var width_err = Array.from(document.getElementsByClassName('width_err'));
var height_err = Array.from(document.getElementsByClassName('height_err'));

// searching for tattoo
search.addEventListener('input', function (){
  if(search.value.length == 0){
    catalogue.classList.replace('justify-content-center', 'justify-content-between');

    for(var i = 0, count = cards.length; i < count; i++){
      cards[i].classList.replace('d-none', 'd-block');
    }
  } else {
    catalogue.classList.replace('justify-content-between', 'justify-content-center');

    for(var i = 0, count = cards.length; i < count; i++){
      item_name = cards[i].href.toLowerCase().split("#").pop();
      if(item_name.indexOf(search.value.toLowerCase().replaceAll(' ', '%20')) > -1){
        cards[i].classList.replace('d-none', 'd-block');
      } else {
        cards[i].classList.replace('d-block', 'd-none');
      }
    }
  }
});

// error reporting
for(let x = 0, count = modal_forms.length; x < count; x++){
  modal_forms[x].addEventListener('change', function(){
    let index = modal_forms.indexOf(this);
    var errors = [];
    
    // validate order quantity
    let value = parseFloat(quantity_fields[index].value);
    let message = quantity_err[index].lastChild;
    if(!Number.isInteger(value) || value < parseInt(quantity_fields[index].min)){
      quantity_fields[index].classList.remove('is-valid');
      quantity_fields[index].classList.add('is-invalid');
      
      message.innerText = (!Number.isInteger(value)) ? "Quantity must be a whole number." : "Quantity must not be less than 1.";
      
      quantity_err[index].classList.replace('d-none', 'd-flex');
      errors.push(message.innerText || message.innerHTML);
    } else {
      quantity_fields[index].classList.remove('is-invalid');
      quantity_fields[index].classList.add('is-valid');
      quantity_err[index].classList.replace('d-flex', 'd-none');
    }

    // validate tattoo width
    value = parseFloat(width_fields[index].value);
    message = width_err[x].lastChild;
    if(!Number.isInteger(value) || value < parseInt(width_fields[index].min) || value > parseInt(width_fields[index].max)){
      width_fields[index].classList.remove('is-valid');
      width_fields[index].classList.add('is-invalid');

      if (!Number.isInteger(value)) message.innerText = "Width must be a whole number.";
      else if(value < parseInt(width_fields[index].min)) message.innerText = "Width must not be less than 1 inch.";
      else message.innerText = "Width must not exceed " + width_fields[index].max + " inches.";

      width_err[index].classList.replace('d-none', 'd-flex');
      errors.push(message.innerText || message.innerHTML);
    } else {
      width_fields[index].classList.remove('is-invalid');
      width_fields[index].classList.add('is-valid');
      width_err[index].classList.replace('d-flex', 'd-none');
    }

    // validate tattoo height
    value = parseFloat(height_fields[index].value);
    message = height_err[x].lastChild;
    if(!Number.isInteger(value) || value < parseInt(height_fields[index].min) || value > parseInt(height_fields[index].max)){
      height_fields[index].classList.remove('is-valid');
      height_fields[index].classList.add('is-invalid');

      if (!Number.isInteger(value)) message.innerText = "Height must be a whole number.";
      else if(value < parseInt(height_fields[index].min)) message.innerText = "Height must not be less than 1 inch.";
      else message.innerText = "Height must not exceed " + height_fields[index].max + " inches.";

      height_err[index].classList.replace('d-none', 'd-flex');
      errors.push(message.innerText || message.innerHTML);
    } else {
      height_fields[index].classList.remove('is-invalid');
      height_fields[index].classList.add('is-valid');
      height_err[index].classList.replace('d-flex', 'd-none');
    }

    // submission guard
    submit_btns[index].disabled = (errors.length > 0) ? true : false;
  });
}