<div class="tweet-form-container">
    <!-- <h2>ツイートする</h2> -->
    <form id="tweet-form" method="POST" action="tweet_create.php" enctype="multipart/form-data">
        <div class="tweet-form-content">
        <textarea name="content" placeholder="What's happening?! "></textarea>
        </div>
        <div class="tweet-form-actions">
            <label for="image-upload" style="cursor: pointer;">
                <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
                <label for="image-upload" style="cursor: pointer;">
                    <span class="material-icons">photo_camera</span> 
                </label>
            </label>
            <input type="file" name="image" id="image-upload" accept="image/*" style="display: none;">
            <button type="submit" style="background-color: #1DA1F2; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">
                POST
            </button>
        </div>
    </form>
</div>

<style>
    .tweet-form-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .tweet-form-content textarea {
        width: 100%;
        height: 60px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        font-size: 16px;
    }

    .tweet-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 10px;
    }

    .tweet-form-actions label img {
        cursor: pointer;
    }

    .tweet-form-actions button {
        background-color: #1DA1F2;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
    }

    .tweet-form-actions button:hover {
        background-color: #0d8bf0;
    }
</style>
