@extends('layouts.app')

@section('title', 'Add Category - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus"></i> Add Category</h2>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">Category Color *</label>
                            <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                   id="color" name="color" value="{{ old('color', '#007bff') }}" required>
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">Icon Class *</label>
                            <select class="form-select @error('icon') is-invalid @enderror" id="icon" name="icon" required>
                                <option value="">Select an icon...</option>
                                
                                <!-- Technology & Electronics -->
                                <optgroup label="📱 Technology & Electronics">
                                    <option value="fas fa-laptop" {{ old('icon') == 'fas fa-laptop' ? 'selected' : '' }}>💻 Laptop</option>
                                    <option value="fas fa-mobile-alt" {{ old('icon') == 'fas fa-mobile-alt' ? 'selected' : '' }}>📱 Mobile Phone</option>
                                    <option value="fas fa-tablet-alt" {{ old('icon') == 'fas fa-tablet-alt' ? 'selected' : '' }}>📱 Tablet</option>
                                    <option value="fas fa-desktop" {{ old('icon') == 'fas fa-desktop' ? 'selected' : '' }}>🖥️ Desktop</option>
                                    <option value="fas fa-tv" {{ old('icon') == 'fas fa-tv' ? 'selected' : '' }}>📺 TV & Monitor</option>
                                    <option value="fas fa-headphones" {{ old('icon') == 'fas fa-headphones' ? 'selected' : '' }}>🎧 Headphones</option>
                                    <option value="fas fa-keyboard" {{ old('icon') == 'fas fa-keyboard' ? 'selected' : '' }}>⌨️ Keyboard</option>
                                    <option value="fas fa-mouse" {{ old('icon') == 'fas fa-mouse' ? 'selected' : '' }}>🖱️ Mouse</option>
                                    <option value="fas fa-microchip" {{ old('icon') == 'fas fa-microchip' ? 'selected' : '' }}>🔧 Hardware</option>
                                    <option value="fas fa-memory" {{ old('icon') == 'fas fa-memory' ? 'selected' : '' }}>💾 Memory</option>
                                    <option value="fas fa-hdd" {{ old('icon') == 'fas fa-hdd' ? 'selected' : '' }}>💿 Storage</option>
                                    <option value="fas fa-plug" {{ old('icon') == 'fas fa-plug' ? 'selected' : '' }}>🔌 Cables & Accessories</option>
                                    <option value="fas fa-battery-full" {{ old('icon') == 'fas fa-battery-full' ? 'selected' : '' }}>🔋 Batteries</option>
                                    <option value="fas fa-wifi" {{ old('icon') == 'fas fa-wifi' ? 'selected' : '' }}>📶 Networking</option>
                                    <option value="fas fa-bluetooth" {{ old('icon') == 'fas fa-bluetooth' ? 'selected' : '' }}>📶 Bluetooth</option>
                                </optgroup>
                                
                                <!-- Gaming & Entertainment -->
                                <optgroup label="🎮 Gaming & Entertainment">
                                    <option value="fas fa-gamepad" {{ old('icon') == 'fas fa-gamepad' ? 'selected' : '' }}>🎮 Gaming</option>
                                    <option value="fas fa-dice" {{ old('icon') == 'fas fa-dice' ? 'selected' : '' }}>🎲 Board Games</option>
                                    <option value="fas fa-puzzle-piece" {{ old('icon') == 'fas fa-puzzle-piece' ? 'selected' : '' }}>🧩 Puzzles</option>
                                    <option value="fas fa-music" {{ old('icon') == 'fas fa-music' ? 'selected' : '' }}>🎵 Music</option>
                                    <option value="fas fa-film" {{ old('icon') == 'fas fa-film' ? 'selected' : '' }}>🎬 Movies</option>
                                    <option value="fas fa-camera" {{ old('icon') == 'fas fa-camera' ? 'selected' : '' }}>📷 Camera</option>
                                    <option value="fas fa-video" {{ old('icon') == 'fas fa-video' ? 'selected' : '' }}>📹 Video</option>
                                </optgroup>
                                
                                <!-- Fashion & Accessories -->
                                <optgroup label="👕 Fashion & Accessories">
                                    <option value="fas fa-tshirt" {{ old('icon') == 'fas fa-tshirt' ? 'selected' : '' }}>👕 Clothing</option>
                                    <option value="fas fa-hat-cowboy" {{ old('icon') == 'fas fa-hat-cowboy' ? 'selected' : '' }}>🎩 Hats</option>
                                    <option value="fas fa-glasses" {{ old('icon') == 'fas fa-glasses' ? 'selected' : '' }}>👓 Eyewear</option>
                                    <option value="fas fa-gem" {{ old('icon') == 'fas fa-gem' ? 'selected' : '' }}>💎 Jewelry</option>
                                    <option value="fas fa-ring" {{ old('icon') == 'fas fa-ring' ? 'selected' : '' }}>💍 Rings</option>
                                    <option value="fas fa-watch" {{ old('icon') == 'fas fa-watch' ? 'selected' : '' }}>⌚ Watches</option>
                                    <option value="fas fa-shoe-prints" {{ old('icon') == 'fas fa-shoe-prints' ? 'selected' : '' }}>👟 Shoes</option>
                                </optgroup>
                                
                                <!-- Home & Garden -->
                                <optgroup label="🏠 Home & Garden">
                                    <option value="fas fa-home" {{ old('icon') == 'fas fa-home' ? 'selected' : '' }}>🏠 Home</option>
                                    <option value="fas fa-couch" {{ old('icon') == 'fas fa-couch' ? 'selected' : '' }}>🛋️ Furniture</option>
                                    <option value="fas fa-bed" {{ old('icon') == 'fas fa-bed' ? 'selected' : '' }}>🛏️ Bedroom</option>
                                    <option value="fas fa-bath" {{ old('icon') == 'fas fa-bath' ? 'selected' : '' }}>🛁 Bathroom</option>
                                    <option value="fas fa-utensils" {{ old('icon') == 'fas fa-utensils' ? 'selected' : '' }}>🍴 Kitchen</option>
                                    <option value="fas fa-blender" {{ old('icon') == 'fas fa-blender' ? 'selected' : '' }}>🥤 Appliances</option>
                                    <option value="fas fa-lightbulb" {{ old('icon') == 'fas fa-lightbulb' ? 'selected' : '' }}>💡 Lighting</option>
                                    <option value="fas fa-seedling" {{ old('icon') == 'fas fa-seedling' ? 'selected' : '' }}>🌱 Garden</option>
                                    <option value="fas fa-leaf" {{ old('icon') == 'fas fa-leaf' ? 'selected' : '' }}>🍃 Plants</option>
                                    <option value="fas fa-tools" {{ old('icon') == 'fas fa-tools' ? 'selected' : '' }}>🔧 Tools</option>
                                </optgroup>
                                
                                <!-- Sports & Fitness -->
                                <optgroup label="⚽ Sports & Fitness">
                                    <option value="fas fa-dumbbell" {{ old('icon') == 'fas fa-dumbbell' ? 'selected' : '' }}>🏋️ Fitness</option>
                                    <option value="fas fa-running" {{ old('icon') == 'fas fa-running' ? 'selected' : '' }}>🏃 Running</option>
                                    <option value="fas fa-bicycle" {{ old('icon') == 'fas fa-bicycle' ? 'selected' : '' }}>🚴 Cycling</option>
                                    <option value="fas fa-swimmer" {{ old('icon') == 'fas fa-swimmer' ? 'selected' : '' }}>🏊 Swimming</option>
                                    <option value="fas fa-football-ball" {{ old('icon') == 'fas fa-football-ball' ? 'selected' : '' }}>🏈 Football</option>
                                    <option value="fas fa-basketball-ball" {{ old('icon') == 'fas fa-basketball-ball' ? 'selected' : '' }}>🏀 Basketball</option>
                                    <option value="fas fa-baseball-ball" {{ old('icon') == 'fas fa-baseball-ball' ? 'selected' : '' }}>⚾ Baseball</option>
                                    <option value="fas fa-tennis-ball" {{ old('icon') == 'fas fa-tennis-ball' ? 'selected' : '' }}>🎾 Tennis</option>
                                    <option value="fas fa-golf-ball" {{ old('icon') == 'fas fa-golf-ball' ? 'selected' : '' }}>⛳ Golf</option>
                                </optgroup>
                                
                                <!-- Food & Beverages -->
                                <optgroup label="🍕 Food & Beverages">
                                    <option value="fas fa-pizza-slice" {{ old('icon') == 'fas fa-pizza-slice' ? 'selected' : '' }}>🍕 Food</option>
                                    <option value="fas fa-coffee" {{ old('icon') == 'fas fa-coffee' ? 'selected' : '' }}>☕ Coffee</option>
                                    <option value="fas fa-wine-glass" {{ old('icon') == 'fas fa-wine-glass' ? 'selected' : '' }}>🍷 Beverages</option>
                                    <option value="fas fa-apple-alt" {{ old('icon') == 'fas fa-apple-alt' ? 'selected' : '' }}>🍎 Fruits</option>
                                    <option value="fas fa-carrot" {{ old('icon') == 'fas fa-carrot' ? 'selected' : '' }}>🥕 Vegetables</option>
                                    <option value="fas fa-bread-slice" {{ old('icon') == 'fas fa-bread-slice' ? 'selected' : '' }}>🍞 Bakery</option>
                                    <option value="fas fa-ice-cream" {{ old('icon') == 'fas fa-ice-cream' ? 'selected' : '' }}>🍦 Desserts</option>
                                </optgroup>
                                
                                <!-- Books & Education -->
                                <optgroup label="📚 Books & Education">
                                    <option value="fas fa-book" {{ old('icon') == 'fas fa-book' ? 'selected' : '' }}>📖 Books</option>
                                    <option value="fas fa-graduation-cap" {{ old('icon') == 'fas fa-graduation-cap' ? 'selected' : '' }}>🎓 Education</option>
                                    <option value="fas fa-pen" {{ old('icon') == 'fas fa-pen' ? 'selected' : '' }}>✏️ Stationery</option>
                                    <option value="fas fa-calculator" {{ old('icon') == 'fas fa-calculator' ? 'selected' : '' }}>🧮 Office Supplies</option>
                                    <option value="fas fa-microscope" {{ old('icon') == 'fas fa-microscope' ? 'selected' : '' }}>🔬 Science</option>
                                    <option value="fas fa-palette" {{ old('icon') == 'fas fa-palette' ? 'selected' : '' }}>🎨 Art Supplies</option>
                                </optgroup>
                                
                                <!-- Health & Beauty -->
                                <optgroup label="💊 Health & Beauty">
                                    <option value="fas fa-pills" {{ old('icon') == 'fas fa-pills' ? 'selected' : '' }}>💊 Medicine</option>
                                    <option value="fas fa-heartbeat" {{ old('icon') == 'fas fa-heartbeat' ? 'selected' : '' }}>❤️ Health</option>
                                    <option value="fas fa-spa" {{ old('icon') == 'fas fa-spa' ? 'selected' : '' }}>🧴 Beauty</option>
                                    <option value="fas fa-cut" {{ old('icon') == 'fas fa-cut' ? 'selected' : '' }}>✂️ Hair Care</option>
                                    <option value="fas fa-tooth" {{ old('icon') == 'fas fa-tooth' ? 'selected' : '' }}>🦷 Dental</option>
                                </optgroup>
                                
                                <!-- Automotive -->
                                <optgroup label="🚗 Automotive">
                                    <option value="fas fa-car" {{ old('icon') == 'fas fa-car' ? 'selected' : '' }}>🚗 Cars</option>
                                    <option value="fas fa-motorcycle" {{ old('icon') == 'fas fa-motorcycle' ? 'selected' : '' }}>🏍️ Motorcycles</option>
                                    <option value="fas fa-truck" {{ old('icon') == 'fas fa-truck' ? 'selected' : '' }}>🚚 Trucks</option>
                                    <option value="fas fa-gas-pump" {{ old('icon') == 'fas fa-gas-pump' ? 'selected' : '' }}>⛽ Fuel</option>
                                    <option value="fas fa-wrench" {{ old('icon') == 'fas fa-wrench' ? 'selected' : '' }}>🔧 Auto Parts</option>
                                    <option value="fas fa-oil-can" {{ old('icon') == 'fas fa-oil-can' ? 'selected' : '' }}>🛢️ Maintenance</option>
                                </optgroup>
                                
                                <!-- General -->
                                <optgroup label="📦 General">
                                    <option value="fas fa-box" {{ old('icon') == 'fas fa-box' ? 'selected' : '' }}>📦 General</option>
                                    <option value="fas fa-tag" {{ old('icon') == 'fas fa-tag' ? 'selected' : '' }}>🏷️ Tag</option>
                                    <option value="fas fa-star" {{ old('icon') == 'fas fa-star' ? 'selected' : '' }}>⭐ Featured</option>
                                    <option value="fas fa-fire" {{ old('icon') == 'fas fa-fire' ? 'selected' : '' }}>🔥 Hot Items</option>
                                    <option value="fas fa-gift" {{ old('icon') == 'fas fa-gift' ? 'selected' : '' }}>🎁 Gifts</option>
                                    <option value="fas fa-heart" {{ old('icon') == 'fas fa-heart' ? 'selected' : '' }}>❤️ Favorites</option>
                                    <option value="fas fa-thumbs-up" {{ old('icon') == 'fas fa-thumbs-up' ? 'selected' : '' }}>👍 Popular</option>
                                    <option value="fas fa-certificate" {{ old('icon') == 'fas fa-certificate' ? 'selected' : '' }}>🏆 Premium</option>
                                </optgroup>
                            </select>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active (category will be available for products)
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-eye"></i> Preview</h5>
            </div>
            <div class="card-body">
                <div id="category-preview" class="card">
                    <div class="card-header text-white" style="background-color: #007bff;">
                        <i class="fas fa-tag"></i> <span id="preview-name">Category Name</span>
                    </div>
                    <div class="card-body">
                        <p id="preview-description" class="text-muted">Category description will appear here...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Guidelines</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Choose a descriptive name</li>
                    <li><i class="fas fa-check text-success"></i> Pick a distinctive color</li>
                    <li><i class="fas fa-check text-success"></i> Select an appropriate icon</li>
                    <li><i class="fas fa-check text-success"></i> Categories are ordered alphabetically</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Live preview
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const colorInput = document.getElementById('color');
    const iconInput = document.getElementById('icon');
    
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    const previewHeader = document.querySelector('#category-preview .card-header');
    const previewIcon = document.querySelector('#category-preview .card-header i');
    
    function updatePreview() {
        previewName.textContent = nameInput.value || 'Category Name';
        previewDescription.textContent = descriptionInput.value || 'Category description will appear here...';
        previewHeader.style.backgroundColor = colorInput.value;
        previewIcon.className = iconInput.value || 'fas fa-tag';
    }
    
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    colorInput.addEventListener('input', updatePreview);
    iconInput.addEventListener('change', updatePreview);
});
</script>
@endsection