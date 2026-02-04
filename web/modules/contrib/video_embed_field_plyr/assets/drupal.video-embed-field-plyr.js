(function (Drupal) {
  Drupal.plyrInstances = (Drupal.plyrInstances)??[];
  Drupal.behaviors.videoEmbedFieldPlyr = {
    attach: function (context) {

         Array.from(document.querySelectorAll("[data-plyr-config]")).map(function(element){

           if (typeof Plyr != 'undefined') {
           var elementId = (element.id||element.parentElement.id) ?? false;
           if (!elementId){
             elementId = 'plyr_'+ Math.random().toString(36).substring(2, 11);
             element.id = elementId;
           }

            Drupal.plyrInstances[elementId] = new Plyr(element);
       }
      });
    }
  };
}(Drupal));
