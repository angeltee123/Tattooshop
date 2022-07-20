// page form
var page_form = document.getElementById('New-tattoo__form');

// page form submit button
var submit = document.getElementById('catalog_tattoo');

// page form fields
var tattoo_name = document.getElementById('tattoo_name');
var price = document.getElementById('tattoo_price');
var description = document.getElementById('tattoo_description');
var color_scheme = document.getElementById('color_scheme');
var complexity_level = document.getElementById('complexity_level');
var width = document.getElementById('tattoo_width');
var height = document.getElementById('tattoo_height');
var image = document.getElementById('image');

// page form fields feedback
var name_err = document.getElementById('name_err');
var price_err = document.getElementById('price_err');
var description_err = document.getElementById('description_err');
var color_scheme_err = document.getElementById('color_scheme_err');
var complexity_level_err = document.getElementById('complexity_level_err');
var width_err = document.getElementById('width_err');
var height_err = document.getElementById('height_err');
var image_err = document.getElementById('image_err');

// error reporting
var errors = [];

var loadFile = function(event){
  var preview = document.getElementById('Preview');
  var previewText = document.getElementById('Preview__text');
  if(image.value.length != 0){
    preview.style.backgroundImage = "url('" + URL.createObjectURL(event.target.files[0]) + "')";
    preview.onload = () => { URL.revokeObjectURL(preview.style.backgroundImage); }
    previewText.classList.add('d-none');
  } else {
    previewText.classList.remove('d-none');
    preview.style.backgroundImage = "none";
  }
};

// resizing fields
window.addEventListener("resize", () => {
  if (window.innerWidth < 1400) {
    description.rows = 2;
    image.classList.remove("form-control-lg");
  } else {
    description.rows = 5;
    image.classList.add("form-control-lg");
  }
});

window.dispatchEvent(new Event("resize"));

// error reporting
page_form.addEventListener('change', function(){
  errors.length = 0;

  // validate tattoo name
  let value = tattoo_name.value.toString().replace(/\s*/g,"").trim();
  let message = name_err.lastChild;
  if(value.length == 0 || value.length > 50 || special_characters.test(value) === true || numbers.test(value) === true){
    tattoo_name.classList.remove('is-valid');
    tattoo_name.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "Tattoo name is required.";
    else if(value.length > 50) message.innerText = "Tattoo name must not exceed 50 characters long.";
    else message.innerText = "Tattoo name must not contain any numbers or special characters.";

    name_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    tattoo_name.classList.remove('is-invalid');
    tattoo_name.classList.add('is-valid');
    name_err.classList.replace('d-flex', 'd-none');
  }

  // validate tattoo price
  value = price.value;
  message = price_err.lastChild;
  if(value.length == 0 || parseFloat(value) < price.min){
    price.classList.remove('is-valid');
    price.classList.add('is-invalid');

    message.innerText = (value.length == 0 || value < price.min) ? "Tattoo price is required." : "Tattoo price must not be negative.";

    price_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    price.classList.remove('is-invalid');
    price.classList.add('is-valid');
    price_err.classList.replace('d-flex', 'd-none');
  }

  // validate tattoo description
  value = description.value.toString().trim();
  message = description_err.lastChild;
  if (value.length == 0) {
    description.classList.remove('is-valid');
    description.classList.add('is-invalid');
    
    message.innerText = "Tattoo description is required.";

    description_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    description.classList.remove('is-invalid');
    description.classList.add('is-valid');
    description_err.classList.replace('d-flex', 'd-none');
  }

  // validate tattoo color scheme
  value = color_scheme.value.toString().trim();
  message = color_scheme_err.lastChild;
  if(value.length == 0 || ["Monochrome", "Multicolor"].includes(value) == false){
    color_scheme.classList.remove('is-valid');
    color_scheme.classList.add('is-invalid');

    message.innerText = (value.length == 0) ? "Color scheme is required." : "Color scheme must be either Monochrome or Multicolor.";

    color_scheme_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    color_scheme.classList.remove('is-invalid');
    color_scheme.classList.add('is-valid');
    color_scheme_err.classList.replace('d-flex', 'd-none');
  }

  // validate tattoo complexity level
  value = complexity_level.value.toString().trim();
  message = complexity_level_err.lastChild;
  if(value.length == 0 || ["Simple", "Complex"].includes(value) == false){
    complexity_level.classList.remove('is-valid');
    complexity_level.classList.add('is-invalid');

    message.innerText = (value.length == 0) ? "Complexity level is required." : "Complexity level must be either Simple or Complex.";

    complexity_level_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    complexity_level.classList.remove('is-invalid');
    complexity_level.classList.add('is-valid');
    complexity_level_err.classList.replace('d-flex', 'd-none');
  }

  // validate tattoo width
  value = parseFloat(width.value);
  message = width_err.lastChild;
  if(!Number.isInteger(value) || value < parseInt(width.min) || value > parseInt(width.max)){
    width.classList.remove('is-valid');
    width.classList.add('is-invalid');

    if (!Number.isInteger(value)) message.innerText = "Width must be a whole number.";
    else if(value < parseInt(width.min)) message.innerText = "Width must not be less than 1 inch.";
    else message.innerText = "Width must not exceed " + width.max + " inches.";

    width_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    width.classList.remove('is-invalid');
    width.classList.add('is-valid');
    width_err.classList.replace('d-flex', 'd-none');
  }

  // validate tattoo height
  value = parseFloat(height.value);
  message = height_err.lastChild;
  if(!Number.isInteger(value) || value < parseInt(height.min) || value > parseInt(height.max)){
    height.classList.remove('is-valid');
    height.classList.add('is-invalid');

    if (!Number.isInteger(value)) message.innerText = "Height must be a whole number.";
    else if(value < parseInt(height.min)) message.innerText = "Height must not be less than 1 inch.";
    else message.innerText = "Height must not exceed " + height.max + " inches.";

    height_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    height.classList.remove('is-invalid');
    height.classList.add('is-valid');
    height_err.classList.replace('d-flex', 'd-none');
  }

  // validate tattoo image
  value = image.files[0];
  message = image_err.lastChild;
  if(image.value.toString().trim().length == 0 || !['image/gif', 'image/jpg', 'image/jpeg', 'image/png'].includes(value.type) || parseInt(value.size) > 50000000){
    image.classList.remove('is-valid');
    image.classList.add('is-invalid');

    if (image.value.toString().trim().length == 0) message.innerText = "Tattoo image is required.";
    else if(!['image/gif', 'image/jpg', 'image/jpeg', 'image/png'].includes(value.type)) message.innerText = "Tattoo image is not a valid image.";
    else message.innerText = "File size is too large.";

    image_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    image.classList.remove('is-invalid');
    image.classList.add('is-valid');
    image_err.classList.replace('d-flex', 'd-none');
  }

  // submission guard
  submit.disabled = (errors.length > 0) ? true : false;
});