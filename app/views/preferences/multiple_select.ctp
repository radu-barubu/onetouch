/**
 * @author zhixin wen <wenzhixin2010@gmail.com>
 */

.ms-parent {
    display: inline-block;
    position: relative;
    vertical-align: middle;
}

.ms-choice {
    display: block;
    height: <?php echo ($isMobile)?'34':'26';?>px;
    padding: 0;
    overflow: hidden;
    cursor: pointer;
    border: 1px solid #aaa;
    text-align: left;
    white-space: nowrap;
    line-height: <?php echo ($isMobile)?'34':'26';?>px;
    color: #444;
    text-decoration: none;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
    background-color: #fff;
    font-size:14px;
}

.ms-choice.disabled {
    background-color: #f4f4f4;
    background-image: none;
    border: 1px solid #ddd;
    cursor: default;
}

.ms-choice > span {
    position: absolute;
    top: 0;
    left: 0;
    right: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    padding-left: 8px;
}

.ms-choice > span.placeholder {
    color: #999;
}

.ms-choice > div {
    position: absolute;
    top: 0;
    right: 0;
    width: 20px;
    height: 25px;
    background: url('/img/multiple-select.png') right top no-repeat;
}

.ms-choice > div.open {
    background: url('/img/multiple-select.png') left top no-repeat;
}

.ms-drop {
    overflow: hidden;
    display: none;
    margin-top: -1px;
    padding: 0;
    position: absolute;
    z-index: 100;
    background: #fff;
    color: #000;
    border: 1px solid #aaa;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
}

.ms-drop.bottom {
    top: 100%;
    -webkit-box-shadow: 0 4px 5px rgba(0, 0, 0, .15);
    -moz-box-shadow: 0 4px 5px rgba(0, 0, 0, .15);
    box-shadow: 0 4px 5px rgba(0, 0, 0, .15);
}

.ms-drop.top {
    bottom: 100%;
    -webkit-box-shadow: 0 -4px 5px rgba(0, 0, 0, .15);
    -moz-box-shadow: 0 -4px 5px rgba(0, 0, 0, .15);
    box-shadow: 0 -4px 5px rgba(0, 0, 0, .15);
}

.ms-search {
    display: inline-block;
    margin: 0;
    min-height: 26px;
    padding: 4px;
    position: relative;
    white-space: nowrap;
    width: 100%;
    z-index: 10000;
}

.ms-search input {
    width: 100%;
    height: auto !important;
    min-height: 24px;
    padding: 0 20px 0 5px;
    margin: 0;
    outline: 0;
    font-family: sans-serif;
    font-size: 1em;
    border: 1px solid #aaa;
    -webkit-border-radius: 0;
    -moz-border-radius: 0;
    border-radius: 0;
    -webkit-box-shadow: none;
    -moz-box-shadow: none;
    box-shadow: none;
    background: #fff url('/img/multiple-select.png') no-repeat 100% -22px;
    background: url('/img/multiple-select.png') no-repeat 100% -22px, -webkit-gradient(linear, left bottom, left top, color-stop(0.85, white), color-stop(0.99, #eeeeee));
    background: url('/img/multiple-select.png') no-repeat 100% -22px, -webkit-linear-gradient(center bottom, white 85%, #eeeeee 99%);
    background: url('/img/multiple-select.png') no-repeat 100% -22px, -moz-linear-gradient(center bottom, white 85%, #eeeeee 99%);
    background: url('/img/multiple-select.png') no-repeat 100% -22px, -o-linear-gradient(bottom, white 85%, #eeeeee 99%);
    background: url('/img/multiple-select.png') no-repeat 100% -22px, -ms-linear-gradient(top, #ffffff 85%, #eeeeee 99%);
    background: url('/img/multiple-select.png') no-repeat 100% -22px, linear-gradient(top, #ffffff 85%, #eeeeee 99%);
}

.ms-search, .ms-search input {
    -webkit-box-sizing: border-box;
    -khtml-box-sizing: border-box;
    -moz-box-sizing: border-box;
    -ms-box-sizing: border-box;
    box-sizing: border-box;
}

.ms-drop ul {
    overflow: auto;
    margin: 0;
    padding: 5px 8px;
}

.ms-drop ul > li {
    list-style: none;
    display: list-item;
    background-image: none;
    position: static;
    font-size:<?php echo ($isMobile)?'18':'14';?>px;
}

.ms-drop ul > li .disabled {
    opacity: .35;
    filter: Alpha(Opacity=35);
}

.ms-drop ul > li.multiple {
    display: block;
    float: left;
}

.ms-drop ul > li.group {
    clear: both;
}

.ms-drop ul > li.multiple label {
    width: 100%;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ms-drop ul > li label.optgroup {
    font-weight: bold;
}

.ms-drop input[type="checkbox"] {
    vertical-align: middle;
}

.ms-drop .ms-no-results {
    display: none;
}
