document.addEventListener('DOMContentLoaded', function(){
  var bodyElement = document.body
  var titleElement = document.getElementById('title');
  var mainElement = document.getElementById('main');
  var mainHeight = window.innerHeight;

  console.log(mainHeight);

  bodyElement.style.height = mainHeight + 'px';
  titleElement.style.height = '10%';
  titleElement.style.lineHeight = mainHeight*0.1 + 'px';
  mainElement.style.height = '90%';

  console.log(titleElement);
  console.log(mainElement);

});