//'VALUE', 'STYLE', 'CSS'
export function render(el, outputAs, data){
    import('./Base.js').then((module) => {
        // console.log(module)
        debugger
        if (outputAs == 'VALUE') {
            el = el.querySelector('input')
        }
        module.render(el, outputAs, data)
    })
    console.log("Input render")
}
