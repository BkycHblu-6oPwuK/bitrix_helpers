<div class="section__container">
    <div class="video">
        <iframe
            width="100%"
            height="840"
            src="<?= $arResult['video_link'] ?>"
            title="YouTube video player"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen>
        </iframe>
        <div class="video__image" id="videoImage">
            <img src="<?= $arResult['preview'] ?>">
        </div>
        <div class="video__info">
            <span class="video__logo"><?= $arResult['title'] ?></span>
            <p class="video__text"><?= $arResult['text'] ?></p>
        </div>
        <div class="video__play-btn" id="playBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="74" height="74" viewBox="0 0 74 74" fill="none">
                <g clip-path="url(#clip0_1358_7325)">
                    <path d="M18.2314 7.462L61.4559 37.0779L18.2314 66.6939L18.2314 7.462Z" fill="white" />
                </g>
                <defs>
                    <clipPath id="clip0_1358_7325">
                        <rect width="73.8462" height="73.8462" fill="white" transform="translate(0.153809 0.0771484)" />
                    </clipPath>
                </defs>
            </svg>
        </div>
    </div>
</div>