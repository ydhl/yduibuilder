// 'HTML' | 'TEXT' | 'VALUE' | 'NAME' | 'STYLE' | 'CSS' | 'SRC'
export function render(el, outputAs, data){
    switch (outputAs){
        case 'TEXT': el.innerText = data; return;
        case 'STYLE': el.style.styleText = data; return;
        case 'CSS': el.classList.add(data); return;
        case 'SRC': el.setAttribute('src', data); return;
        case 'VALUE': el.setAttribute('value', data); return;
        case 'HTML':
        default: el.innerHTML = data; return;
    }
    console.log("Base render")
}
