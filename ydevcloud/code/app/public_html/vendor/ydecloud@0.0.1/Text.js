//'TEXT', 'HTML', 'STYLE', 'CSS'
export function render(el, outputAs, data){
    import('./Base.js').then((module) => {
        // console.log(module)
        module.render(el, outputAs, data)
    })
    console.log("Text render")
}
