// /**=====================
//     Number Count Js
// ==========================**/

let counted = 0;

window.addEventListener('scroll', function() {
  const counter = document.querySelector('.counter');
  
  if (counter) {
    const oTop = counter.offsetTop - window.innerHeight;

    if (counted === 0 && window.scrollY > oTop) {
      const counts = document.querySelectorAll('.count');
      
      counts.forEach(function(countElement) {
        const countTo = parseInt(countElement.getAttribute('data-to'));
        let countNum = 0;

        const animateCount = () => {
          const increment = countTo / 200; // Controls the speed of counting
          countNum = Math.min(countNum + increment, countTo);
          countElement.textContent = Math.floor(countNum);
          
          if (countNum < countTo) {
            requestAnimationFrame(animateCount);
          } else {
            countElement.textContent = countTo;
          }
        };
        
        requestAnimationFrame(animateCount);
      });

      counted = 1;
    }
  }
});

