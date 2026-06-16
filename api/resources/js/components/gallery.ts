let imageList: string[] = [];

const deleteImage = (imageId: string) => {
    if (confirm('画像を削除しても良いですか?')) {
        imageList = imageList.filter((id) => id !== imageId);
        // 画像を削除する(親ごと削除)
        const imageElement = document.querySelector(`.image-gallery[data-id="${imageId}"]`) as HTMLElement;
        if (imageElement && imageElement.parentNode) {
          (imageElement.parentNode as Element).remove();
        }
    }
}

interface Window {
  deleteImage: (imageId: string) => void;
}

window.onload = function() {
    Array.from(document.getElementsByClassName('image-gallery')).forEach((element) => {
        const dataId = element.getAttribute('data-id');
        if (dataId !== null) {
            imageList.push(dataId);
        }
    });
}

window.deleteImage = deleteImage;
