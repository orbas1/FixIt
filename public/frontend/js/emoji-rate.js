// emoji rate js
var a = document.querySelectorAll(".emoji-tab .emoji-icon .emojis");
for (var i = 0, length = a.length; i < length; i++) {
    a[i].onclick = function() {
        var b = document.querySelector(".emoji-tab .emoji-icon.active");
        if (b) b.classList.remove("active");
        this.parentNode.classList.add('active');
    };
}