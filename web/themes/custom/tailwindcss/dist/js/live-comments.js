(function (Pusher, Drupal, drupalSettings, once) {
    Drupal.behaviors.liveComments = {
        attach: function (context) {
            const commentForm = document.getElementById('comment-form');
            const commentsList = document.getElementById('comments-list');
            const playCommentTemplate = document.getElementById('play-comment-template');
            once('liveComments', document.querySelector('#comments-list'), context).forEach(function () {
                // Enable pusher logging - don't include this in production
                Pusher.logToConsole = true;
                const nid = drupalSettings.sports_comments.nid;
                const serverUrl = "/",
                    comments = [],
                    pusher = new Pusher('c428b53f1cbc41e05ffe', {
                        cluster: 'us2',
                        encrypted: true
                    }),
                    // Subscribing to the 'sp0rts-comments' Channel.
                    channel = pusher.subscribe('sp0rts-comments');


                // Binding to Pusher Event on our 'sp0rts-comments' Channel.
                let pusherEvent = 'new_play_' + nid;
                console.log(pusherEvent);
                channel.bind(pusherEvent, newCommentReceived);
            });


            // Adding to Comment Form Submit Event
            // commentForm.addEventListener("submit", addNewComment);

            // New Comment Receive Event Handler
            // We will take the Comment Template, replace placeholders & append to commentsList
            function newCommentReceived(data) {

                // If data has a play_title property, this is a play comment.
                console.log(data);
                if (data.play_title) {
                    let newCommentHtml = playCommentTemplate.innerHTML.replace('[[play_title]]', data.play_title);
                    newCommentHtml = newCommentHtml.replace('[[play_type]]', data.play_type);
                    newCommentHtml = newCommentHtml.replace('[[play_quarter]]', data.play_quarter);
                    newCommentHtml = newCommentHtml.replace('[[play_time]]', data.play_time);
                    newCommentHtml = newCommentHtml.replace('[[play_t1_logo]]', data.play_t1_logo);
                    newCommentHtml = newCommentHtml.replace('[[play_t2_logo]]', data.play_t2_logo);
                    newCommentHtml = newCommentHtml.replace('[[play_score]]', data.play_score);
                    if (data.play_down) {
                        let dandd = '<span class="play-down">'+
                            data.play_down +
                            '</span> and <span class="play-distance">'+
                            data.play_distance +
                            '</span>';
                        newCommentHtml = newCommentHtml.replace('[[play_dandd]]', dandd);
                    }
                    else {
                        newCommentHtml = newCommentHtml.replace('[[play_dandd]]', '');
                    }
                    newCommentHtml = newCommentHtml.replace('[[play_scored]]', data.play_scored);
                    newCommentHtml = newCommentHtml.replace('[[play_timestamp]]', data.play_timestamp);
                    const newCommentNode = document.createElement('div');
                    newCommentNode.classList.add('comment m-2');
                    newCommentNode.innerHTML = newCommentHtml;
                    let firstComment = commentsList.firstChild;
                    commentsList.insertBefore(newCommentNode, firstComment);
                }
            }

            function addNewComment(event) {
                event.preventDefault();
                const newComment = {
                    "name": document.getElementById('new_comment_name').value,
                    "comment": document.getElementById('new_comment_text').value
                };

                const xhr = new XMLHttpRequest();
                xhr.open("POST", serverUrl + "comment", true);
                xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState !== 4 || xhr.status !== 200) return;

                    // On Success of creating a new Comment
                    console.log("Success: " + xhr.responseText);
                    commentForm.reset();
                };
                xhr.send(JSON.stringify(newComment));
            }
        }
    }
})(Pusher, Drupal, drupalSettings, once);
