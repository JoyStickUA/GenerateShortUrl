document.addEventListener('DOMContentLoaded',ready);
function ready(argument){
    document.addEventListener('click',documentOnClickFunction);
}function documentOnClickFunction(e){
    var e=e||event;
    if(e.target.classList.contains('create_link')){
        var el= e.target;
        document.querySelector('#'+el.getAttribute('data-tab-id')).classList.add('hide');
        document.querySelector('#'+el.getAttribute('data-id')).classList.remove('hide');
    }
    if(e.target.classList.contains('close_popup')){
        var el= e.target;
        document.querySelector('#'+el.getAttribute('data-id')).classList.add('hide');
    }
}
