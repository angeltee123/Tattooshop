// search bar
var search = document.getElementById('search');

// new tattoo card
var new_tattoo = document.getElementById('new_tattoo');

// catalogue
var catalogue = document.getElementById('Catalogue');
var cards = document.getElementsByClassName('Catalogue__cards__card');

// modal forms
var modal_forms = Array.from(document.getElementsByClassName('Catalogue__cards__modal__form'));

// modal form fields
var name_fields = Array.from(document.getElementsByName('tattoo_name'));
var price_fields = Array.from(document.getElementsByName('tattoo_price'));
var description_fields = Array.from(document.getElementsByName('tattoo_description'));
var color_scheme_selects = Array.from(document.getElementsByName('color_scheme'));
var complexity_level_selects = Array.from(document.getElementsByName('complexity_level'));
var width_fields = Array.from(document.getElementsByName('tattoo_width'));
var height_fields = Array.from(document.getElementsByName('tattoo_height'));
var image_fields = Array.from(document.getElementsByName('image'));
var submit_btns = Array.from(document.getElementsByName('update_tattoo'));

// modal form fields feedback
var name_err = Array.from(document.getElementsByClassName('name_err'));
var price_err= Array.from(document.getElementsByClassName('price_err'));
var description_err = Array.from(document.getElementsByClassName('description_err'));
var color_scheme_err = Array.from(document.getElementsByClassName('color_scheme_err'));
var complexity_level_err = Array.from(document.getElementsByClassName('complexity_level_err'));
var width_err = Array.from(document.getElementsByClassName('width_err'));
var height_err = Array.from(document.getElementsByClassName('height_err'));
var image_err = Array.from(document.getElementsByClassName('image_err'));

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
      if(item_name.indexOf(search.value.toLowerCase().replaceAll(' ', '%20')) > -1 && cards[i] !== new_tattoo){
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

    // validate tattoo name
    let value = name_fields[index].value.toString().replace(/\s*/g,"").trim();
    let message = name_err[index].lastChild;
    if(value.length == 0 || value.length > 50 || special_characters.test(value) === true || numbers.test(value) === true){
      name_fields[index].classList.remove('is-valid');
      name_fields[index].classList.add('is-invalid');

      if (value.length == 0) message.innerText = "Tattoo name is required.";
      else if(value.length > 50) message.innerText = "Tattoo name must not exceed 50 characters long.";
      else message.innerText = "Tattoo name must not contain any numbers or special characters.";

      name_err[index].classList.replace('d-none', 'd-flex');
      errors.push(message.innerText || message.innerHTML);
    } else {
      name_fields[index].classList.remove('is-invalid');
      name_fields[index].classList.add('is-valid');
      name_err[index].classList.replace('d-flex', 'd-none');
    }

    // validate tattoo price
    value = price_fields[index].value;
    message = price_err[index].lastChild;
    if(value.length == 0 || parseFloat(value) < price_fields[index].min){
      price_fields[index].classList.remove('is-valid');
      price_fields[index].classList.add('is-invalid');

      message.innerText = (value.length == 0 || value < price_fields[index].min) ? "Tattoo price is required." : "Tattoo price must not be negative.";

      price_err[index].classList.replace('d-none', 'd-flex');
      errors.push(message.innerText || message.innerHTML);
    } else {
      price_fields[index].classList.remove('is-invalid');
      price_fields[index].classList.add('is-valid');
      price_err[index].classList.replace('d-flex', 'd-none');
    }

    // validate tattoo description
    value = description_fields[index].value.toString().trim();
    message = description_err[index].lastChild;
    if (value.length == 0) {
      description_fields[index].classList.remove('is-valid');
      description_fields[index].classList.add('is-invalid');
      
      message.innerText = "Tattoo description is required.";

      description_err[index].classList.replace('d-none', 'd-flex');
      errors.push(message.innerText || message.innerHTML);
    } else {
      description_fields[index].classList.remove('is-invalid');
      description_fields[index].classList.add('is-valid');
      description_err[index].classList.replace('d-flex', 'd-none');
    }

    // validate tattoo color scheme
    value = color_scheme_selects[index].value.toString().trim();
    message = color_scheme_err[index].lastChild;
    if(value.length == 0 || ["Monochrome", "Multicolor"].includes(value) == false){
      color_scheme_selects[index].classList.remove('is-valid');
      color_scheme_selects[index].classList.add('is-invalid');

      message.innerText = (value.length == 0) ? "Color scheme is required." : "Color scheme must be either Monochrome or Multicolor.";

      color_scheme_err[index].classList.replace('d-none', 'd-flex');
      errors.push(message.innerText || message.innerHTML);
    } else {
      color_scheme_selects[index].classList.remove('is-invalid');
      color_scheme_selects[index].classList.add('is-valid');
      color_scheme_err[index].classList.replace('d-flex', 'd-none');
    }

    // validate tattoo complexity level
    value = complexity_level_selects[index].value.toString().trim();
    message = complexity_level_err[index].lastChild;
    if(value.length == 0 || ["Simple", "Complex"].includes(value) == false){
      complexity_level_selects[index].classList.remove('is-valid');
      complexity_level_selects[index].classList.add('is-invalid');

      message.innerText = (value.length == 0) ? "Complexity level is required." : "Complexity level must be either Simple or Complex.";

      complexity_level_err[index].classList.replace('d-none', 'd-flex');
      errors.push(message.innerText || message.innerHTML);
    } else {
      complexity_level_selects[index].classList.remove('is-invalid');
      complexity_level_selects[index].classList.add('is-valid');
      complexity_level_err[index].classList.replace('d-flex', 'd-none');
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

    // validate tattoo image
    value = image_fields[index];
    message = image_err[index].lastChild;
    if(value.value.toString().trim().length > 0){
      if(!['image/gif', 'image/jpg', 'image/jpeg', 'image/png'].includes(value.files[0].type) || parseInt(value.files[0].size) > 50000000){
        value.classList.remove('is-valid');
        value.classList.add('is-invalid');

        message.innerText = (!['image/gif', 'image/jpg', 'image/jpeg', 'image/png'].includes(value.files[0].type)) ? "Tattoo image is not a valid image." : "File size is too large.";

        image_err[index].classList.replace('d-none', 'd-flex');
        errors.push(message.innerText || message.innerHTML);
      } else {
        value.classList.remove('is-invalid');
        value.classList.add('is-valid');
        image_err[index].classList.replace('d-flex', 'd-none');
      }
    }

    // submission guard
    submit_btns[index].disabled = (errors.length > 0) ? true : false;
  });
}