class Zouma {
    constructor(class_name,time=30) {
		this.time = time; // 定时器，每30毫秒执行一次move函数
        this.init(class_name);
    }

    init(class_name) {
        // 初始化元素
        this.ul = document.querySelector("."+class_name+" ul");
		this.ul_box = document.querySelector("."+class_name);
		this.ul_box_width = this.ul_box.offsetWidth;
		// 克隆图片实现无缝滚动
		this.ul.innerHTML = this.ul.innerHTML + this.ul.innerHTML;
        this.lis = this.ul.querySelectorAll("li");
        this.spa = -2; // 默认向左滚动
        this.ul.style.width = `${this.lis[0].offsetWidth * this.lis.length}px`;
    }
	
    move() {
				
        if (this.ul.offsetLeft <= -this.ul.offsetWidth) {
			this.ul.style.left = `${this.ul_box_width}px`;
        }else if(this.ul.offsetLeft > 0 && 0){
			this.ul.style.left = `${-this.ul.offsetWidth / 2}px`;
		}else{
			this.ul.style.left = `${this.ul.offsetLeft + this.spa}px`;
		}
    }
    
    start() {
        this.timer = setInterval(() => this.move(), this.time);
		this.bindEvents()
    }
    
    stop() {
        clearInterval(this.timer);
    }
    
    bindEvents() {
        this.ul.addEventListener('mousemove', this.stop.bind(this));
        this.ul.addEventListener('mouseout', () => {
            this.timer = setInterval(() => this.move(), this.time);
        });
    }
}

// 使用示例
// const carousel = new Zouma("类名","调用时间（h毫秒）");
// carousel.start();
