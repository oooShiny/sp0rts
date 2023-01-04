// Using IIFE for Implementing Module Pattern to keep the Local Space for the JS Variables
(function() {
    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var serverUrl = "/",
        comments = [],
        pusher = new Pusher('c428b53f1cbc41e05ffe', {
            cluster: 'us2',
            encrypted: true
        }),
        // Subscribing to the 'flash-comments' Channel
        channel = pusher.subscribe('sp0rts-comments'),
        commentForm = document.getElementById('comment-form'),
        commentsList = document.getElementById('comments-list'),
        playCommentTemplate = document.getElementById('play-comment-template');

    // Binding to Pusher Event on our 'flash-comments' Channel
    channel.bind('new_play',newCommentReceived);

    // Adding to Comment Form Submit Event
    commentForm.addEventListener("submit", addNewComment);

    // New Comment Receive Event Handler
    // We will take the Comment Template, replace placeholders & append to commentsList
    function newCommentReceived(data){
        var newCommentHtml = playCommentTemplate.innerHTML.replace('{{ play_title }}', data.play_title);
        newCommentHtml = newCommentHtml.replace('{{ play_quarter }}', data.play_quarter);
        newCommentHtml = newCommentHtml.replace('{{ play_time }}', data.play_time);
        newCommentHtml = newCommentHtml.replace('{{ play_t1_logo }}', data.play_t1_logo);
        newCommentHtml = newCommentHtml.replace('{{ play_t2_logo }}', data.play_t2_logo);
        newCommentHtml = newCommentHtml.replace('{{ play_score }}', data.play_score);
        newCommentHtml = newCommentHtml.replace('{{ play_down }}', data.play_down);
        newCommentHtml = newCommentHtml.replace('{{ play_distance }}', data.play_distance);
        var newCommentNode = document.createElement('div');
        newCommentNode.classList.add('comment');
        newCommentNode.innerHTML = newCommentHtml;
        commentsList.appendChild(newCommentNode);
    }

    function addNewComment(event){
        event.preventDefault();
        var newComment = {
            "name": document.getElementById('new_comment_name').value,
            "email": document.getElementById('new_comment_email').value,
            "comment": document.getElementById('new_comment_text').value
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", serverUrl+"comment", true);
        xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xhr.onreadystatechange = function () {
            if (xhr.readyState != 4 || xhr.status != 200) return;

            // On Success of creating a new Comment
            console.log("Success: " + xhr.responseText);
            commentForm.reset();
        };
        xhr.send(JSON.stringify(newComment));
    }

})();
