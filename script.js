// Funkcja generująca losowe położenie dla każdego obrazka
document.addEventListener("DOMContentLoaded", function() {
    const images = document.querySelectorAll('.floating-item');
    
    images.forEach(image => {
        // Losowe położenie w poziomie (0% do 100%)
        const randomX = Math.floor(Math.random() * 100);
        // Losowe położenie w pionie (0% do 100%)
        const randomY = Math.floor(Math.random() * 100);
        
        // Losowy rozmiar (pomiedzy 50px a 150px)
        const randomSize = Math.floor(Math.random() * 100) + 50;
        
        // Losowy kąt obrotu (0 do 360 stopni)
        const randomRotate = Math.floor(Math.random() * 360);

        // Ustawiamy styl na losowe położenie, rozmiar i kąt obrotu
        image.style.top = `${randomY}%`;
        image.style.left = `${randomX}%`;
        image.style.width = `${randomSize}px`;
        image.style.height = `${randomSize}px`;
        image.style.transform = `rotate(${randomRotate}deg)`;
    });
});
