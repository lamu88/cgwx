function fitImage(objDiv, objImg) {				
	var divWidth = objDiv.offsetWidth;      //容器的宽
	var divHeight = objDiv.offsetHeight;    //容器的高			
	var imgWidth = objImg.width;    //图片的宽
	var imgHeight = objImg.height; //图片的高
	var imgScale = '';   //图片缩放倍数

	var divRatio = (divWidth / divHeight);  //容器宽高比
	var imgRatio = (imgWidth / imgHeight);  //图片宽高比
	
	if(divRatio >= imgRatio){
		imgScale = divWidth / imgWidth;
	}else{
		imgScale = divHeight / imgHeight;
	}
	
	objImg.style.width = (imgWidth * imgScale) + 'px';
	objImg.style.height = (imgHeight * imgScale) + 'px';
	
	objImg.style.marginLeft = (divWidth - objImg.width) / 2 + 'px';
	objImg.style.marginTop = (divHeight - objImg.height) / 2 + 'px';	
}
