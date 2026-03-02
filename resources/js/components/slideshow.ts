const changeSlide = (n: number) => {
    slideIndex += n;
    showSlides();
}

const showSlides = () => {
    let slides = document.getElementsByClassName("slide") as HTMLCollectionOf<HTMLElement>;

    if (slides.length === 0) return;

    if (slideIndex >= slides.length) {
        slideIndex = 0
    }
    if (slideIndex < 0) {
        slideIndex = slides.length - 1
    }

    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    slides[slideIndex].style.display = "block";
}

let slideIndex:  number = 0;
showSlides();

// 関数をグローバルスコープに公開する
(window as any).changeSlide = changeSlide;
(window as any).showSlides = showSlides;