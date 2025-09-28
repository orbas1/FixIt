//  Add favourite 
var addBtns = document.getElementsByClassName('favourite');
for (var i = 0; i < addBtns.length; i++) {
    addBtns[i].addEventListener('click', function () {
        var noLike = this.parentNode.querySelector('.nolike');
        var like = this.parentNode.querySelector('.like');
        
        noLike.classList.toggle('hide');
        like.classList.toggle('show');
    });
}