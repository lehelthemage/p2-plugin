/**
 *	File: WPPost.js
 *	Description: javascript class definition for Wordpress Live Feed Posts
 *	Author: Lehel Kovach
 */

function WPComment(_author, _date, _text) {
	this.author = _author;
	this.date = _date;
	this.text = _text;
}

function WPPost(_author, _date, _subject, _body) {
	this.author = _author;
	this.date = _date;
	this.subject = _subject;
	this.body = _body;
	this.comments = new Array();
}

WPPost.prototype.addComment = function(wpComment) {
	this.comments.push(wpComment);
};



function WPPostList() {
	this.posts = new Array(); //list of posts
}

WPPostList.prototype.addPost = function(wpPost) {
	this.posts.push(wpPost);
};



