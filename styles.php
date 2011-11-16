#mod-lightboxgallery-view .generalbox,
#mod-lightboxgallery-search .generalbox {
  overflow: auto;
}

#mod-lightboxgallery-view .thumb,
#mod-lightboxgallery-search .thumb {
  position: relative;
  z-index: 5;
  border: 1px solid #ccc;
  background-color: #fff;
  float: left;
  text-align: center;
  margin: 2px;
  padding: 3px;
}

#mod-lightboxgallery-view .thumb .image,
#mod-lightboxgallery-search .thumb .image {
  position: relative;
  z-index: 10;
  border: 1px solid #ccc;
  background-color: #000;
  height: 105px;
  width: 120px;
  margin-bottom: 2px;
}

#mod-lightboxgallery-view .thumb .overlay img,
#mod-lightboxgallery-search .thumb .overlay img {
  border: 0;
}

#mod-lightboxgallery-view .lightbox-edit-select {
  margin: 4px;
}

#mod-lightboxgallery-imageedit .generaltable img,
#mod-lightboxgallery-imageadd .generaltable img {
  border: 1px solid #ddd;
}

#mod-lightboxgallery-imageedit .menubar {
  margin-top: 14px;
  text-align: center;
}

#mod-lightboxgallery-imageedit .tag-head {
  border-bottom: 1px solid #ddd;
  background-color: #f9fafa;
  display: block;
  padding: 2px 0;
  margin: 3px 1px;
}

#mod-lightboxgallery-imageedit .tag-exists {
  color: #aaa;
  text-decoration: line-through;
}

#mod-lightboxgallery-imageedit .tag-exists input {
  display: none;
}

#mod-lightboxgallery-imageadd #messages {
  margin: 0 6px 0 12px;
  padding: 0;
}

#mod-lightboxgallery-search .generalbox {
   margin-bottom: 10px;
}


/* SLIMBOX */

#lbOverlay {
	position: fixed;
	z-index: 9999;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: #000;
	cursor: pointer;
}

#lbCenter, #lbBottomContainer {
	position: absolute;
	z-index: 9999;
	overflow: hidden;
	background-color: #fff;
}

.lbLoading {
	background: #fff url(../../mod/lightboxgallery/img/loading.gif) no-repeat center;
}

#lbImage {
	position: absolute;
	left: 0;
	top: 0;
	border: 10px solid #fff;
	background-repeat: no-repeat;
}

#lbPrevLink, #lbNextLink {
	display: block;
	position: absolute;
	top: 0;
	width: 50%;
	outline: none;
}

#lbPrevLink {
	left: 0;
}

#lbPrevLink:hover {
	background: transparent url(../../mod/lightboxgallery/img/prevlabel.gif) no-repeat 0 15%;
}

#lbNextLink {
	right: 0;
}

#lbNextLink:hover {
	background: transparent url(../../mod/lightboxgallery/img/nextlabel.gif) no-repeat 100% 15%;
}

#lbBottom {
	font-family: Verdana, Arial, Geneva, Helvetica, sans-serif;
	font-size: 10px;
	color: #666;
	line-height: 1.4em;
	text-align: left;
	border: 10px solid #fff;
	border-top-style: none;
}

#lbCloseLink {
	display: block;
	float: right;
	width: 66px;
	height: 22px;
	background: transparent url(../../mod/lightboxgallery/img/closelabel.gif) no-repeat center;
	margin: 5px 0;
	outline: none;
}

#lbCaption, #lbNumber {
	margin-right: 71px;
}

#lbCaption {
	font-weight: bold;
}

/* JCrop */

/* Fixes issue here http://code.google.com/p/jcrop/issues/detail?id=1 */
.jcrop-holder { text-align: left; }

.jcrop-vline, .jcrop-hline
{
	font-size: 0;
	position: absolute;
	background: white url('../../mod/lightboxgallery/img/Jcrop.gif') top left repeat;
}
.jcrop-vline { height: 100%; width: 1px !important; }
.jcrop-hline { width: 100%; height: 1px !important; }
.jcrop-handle {
	font-size: 1px;
	width: 7px !important;
	height: 7px !important;
	border: 1px #eee solid;
	background-color: #333;
	*width: 9px;
	*height: 9px;
}

.jcrop-tracker { width: 100%; height: 100%; }

.custom .jcrop-vline,
.custom .jcrop-hline
{
	background: yellow;
}
.custom .jcrop-handle
{
	border-color: black;
	background-color: #C7BB00;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
}
