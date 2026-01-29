<!-- Portfolio Post Modal -->
<div id="portfolioPostModal" class="portfolio-post-modal">
    <div class="modal-overlay" id="portfolioModalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fi fi-rr-edit"></i>
                <span>Create Portfolio Post</span>
            </h2>
            <button class="modal-close-btn" id="closePortfolioModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <form id="portfolioPostForm" method="POST" action="/contractor/portfolio" enctype="multipart/form-data">
                @csrf

                <!-- Success/Error Messages -->
                <div id="portfolioFormSuccess" class="alert alert-success hidden"></div>
                <div id="portfolioFormError" class="alert alert-error hidden"></div>

                <!-- Portfolio Title -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-file-edit"></i>
                        <span>Portfolio Title <span class="required">*</span></span>
                    </label>
                    <input type="text" name="portfolio_title" id="portfolio_title" class="form-input" placeholder="Enter portfolio title" required maxlength="200">
                    <div class="error-message hidden" id="error_portfolio_title"></div>
                </div>

                <!-- Portfolio Description -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-document"></i>
                        <span>Description <span class="required">*</span></span>
                    </label>
                    <textarea name="portfolio_description" id="portfolio_description" class="form-textarea" placeholder="Describe your portfolio work in detail..." required rows="5"></textarea>
                    <div class="error-message hidden" id="error_portfolio_description"></div>
                </div>

                <!-- Portfolio Image -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-image"></i>
                        <span>Portfolio Image <span class="required">*</span></span>
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="portfolio_image" name="portfolio_image" accept=".jpg,.jpeg,.png" class="file-input" required>
                        <label for="portfolio_image" class="file-upload-label">
                            <i class="fi fi-rr-upload"></i>
                            <span>Choose Portfolio Image</span>
                        </label>
                        <div class="file-name-display" id="portfolio_image_name"></div>
                    </div>
                    <div class="image-preview-container hidden" id="portfolio_image_preview">
                        <img id="portfolio_image_preview_img" src="" alt="Portfolio preview" class="image-preview">
                        <button type="button" class="remove-image-btn" id="remove_portfolio_image">
                            <i class="fi fi-rr-cross-small"></i>
                        </button>
                    </div>
                    <small class="form-hint">Accepted: JPG, JPEG, PNG (Max 10MB)</small>
                    <div class="error-message hidden" id="error_portfolio_image"></div>
                </div>

                <!-- Location (Optional) -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-marker"></i>
                        <span>Location (Optional)</span>
                    </label>
                    <input type="text" name="portfolio_location" id="portfolio_location" class="form-input" placeholder="e.g., Manila, Philippines" maxlength="255">
                    <div class="error-message hidden" id="error_portfolio_location"></div>
                </div>

                <!-- Tags/Categories (Optional) -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-tag"></i>
                        <span>Tags (Optional)</span>
                    </label>
                    <input type="text" name="portfolio_tags" id="portfolio_tags" class="form-input" placeholder="e.g., Residential, Modern, Commercial (comma-separated)" maxlength="200">
                    <small class="form-hint">Separate tags with commas</small>
                    <div class="error-message hidden" id="error_portfolio_tags"></div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" id="cancelPortfolioModalBtn">Cancel</button>
                    <button type="submit" class="btn btn-submit" id="submitPortfolioBtn">
                        <i class="fi fi-rr-check"></i>
                        <span>Post Portfolio</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
