//hàm xử lý hiệu ứng khi thêm
let btnThemVaoGio = document.querySelectorAll('.btn-them-vao-gio')
let count = 0
function themGioHang(){
    count++
    console.log(count)
    let soLuongItem = document.querySelector('#gio-hang')
    if(count === 1){
        let icon = document.querySelector('.gio-hang > i')
        icon.style.color = 'green'
        soLuongItem.style.backgroundColor = 'green'
    }
    else if(count === 0 ){
        let icon = document.querySelector('.gio-hang > i')
        icon.style.color = 'black'
        soLuongItem.style.backgroundColor = 'black'
    }
    soLuongItem.innerText = count
}
for(let i of btnThemVaoGio){
    i.onclick = themGioHang
}

// event best seller
const btnPre = document.querySelector('#btn-pre')
const btnNext = document.querySelector('#btn-next')
let percent = 0
let gap = 0

// dư sản phầm --> không cho animation
let maxCount = 3
function dichChuyenSangTrai(className){
    if(gap < maxCount){
        percent+=100
        gap+=1
        let listSP = document.querySelectorAll(`.${className}`)
        if(listSP)
            maxCount = listSP.length - 4

        for(let i of listSP){
            i.style.transform = `translateX(calc(-${percent}% - ${gap}rem))`
        }
    }
}
    
function dichChuyenSangPhai(className){
    if(gap <= maxCount && gap !== 0){
        percent-=100
        gap-=1
        let listSP = document.querySelectorAll(`.${className}`)
        if(listSP)
            maxCount = listSP.length - 4

        for(let i of listSP){
            i.style.transform = `translateX(calc(-${percent}% - ${gap}rem))`
        }
    }
}

// add event
if(btnNext && btnPre){
    btnNext.onclick = (e)=>{
        dichChuyenSangTrai('san-pham')
    }
    btnPre.addEventListener('click',(e)=>{
       dichChuyenSangPhai('san-pham')
    })
} 


//event trang chi tiet san pham

const btnPre2 = document.querySelector('#btn-pre-2')
const btnNext2 = document.querySelector('#btn-next-2')
if(btnNext2 && btnPre2){

    btnNext2.onclick = (e)=>{
        dichChuyenSangTrai('san-pham-gioi-thieu')
    }
    btnPre2.addEventListener('click',(e)=>{
       dichChuyenSangPhai('san-pham-gioi-thieu')
    })
}

// js trang chi tiet san pham
const btn = document.querySelector('#product-info')
function toggleProductInfo(button){
    if(button.innerText == '+'){
        let info = document.querySelector('#chi-tiet-mo-ta')
        info.style.height = '150px'
        button.innerText = '-'
    }
    else{
        let info = document.querySelector('#chi-tiet-mo-ta')
        info.style.height = '0px'
        button.innerText = '+'
    }
    
}
btn.onclick = (e) => {
    toggleProductInfo(e.target)
}

// thong bao 
function thongBaoThemVaoGio(state){
    let index = state == 1 ? 0 : 1;
    let wrap = document.querySelector('.thong-bao')
    let arrIcon = ['check','xmark']
    let arrClass = ["sucess","error"]
    let arrText = ['Thêm vào giỏ hàng thành công','Thêm vào giỏ hàng thất bại']
    let item = document.createElement('div')
    item.classList.add('notify')
    item.innerHTML = `<div class="icon ${arrClass[index]}">
                        <i class="fa-solid fa-${arrIcon[index]}"></i>
                    </div>

                    <div class="text">
                        <strong>${arrText[index]}</strong>
                    </div>`;
    wrap.appendChild(item);
    setTimeout(()=>{
       wrap.removeChild(item)
    },3000)
}

// modal
let btnGio = document.querySelector('#btn-gio')
btnGio.onclick = (e) => {
    thongBaoThemVaoGio(true)
}