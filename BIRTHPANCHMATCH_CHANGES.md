# Changes Required for BIRTHPANCHMATCH Package

## Summary
The new package `BIRTHPANCHMATCH` combines:
- Birth Chart functionality (BIRTH)
- Panchang functionality (PANCHANG  
- Matching functionality (MATCHING)

## Files Already Updated
1. ✅ `lib.php` - Added BIRTHPANCHMATCH constant (line 39)
2. ✅ `lib.php` - Added to $usertype_arr (line 54)
3. ✅ `lib.php` - Added to checkUserType function (line 6895)
4. ✅ `lib.php` - Added case in homepageMenu function (after line 6863)
5. ✅ `birthChartMenu_mobile.php` - Added menu case (after BIRTHPANCH)

## Files That Need Manual Updates

### 1. matchingChartMenu_mobile.php
**Location**: After line ~49 (after BIRTHMATCH case)
**Add**:
```php
elseif(strcmp($usertype,BIRTHPANCHMATCH)==0){
    echo <<<EOL
	 <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
				  <a class='glow-button' href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a class='glow-button' href="Matching_input.php">திருமண பொருத்தம்</a>
				  <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
				  <a class='glow-button' href="hora.php">ஓரை</a>
				  <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</a>
				</div>
          </div> 
			<a href="matchingChartDisplay.php"><i class="fa fa-history fa-2x"></i></a>
			<div class='menuHeading'>பொருத்தம்</div>
		</div>
	</div>

EOL;
}
```

### 2. panchangMenu_mobile.php
**Location**: After line ~105 (after BIRTHPANCH case)
**Add**:
```php
elseif(strcmp($usertype,BIRTHPANCHMATCH)==0){
    echo <<<EOL
	 <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
				  <a class='glow-button' href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a class='glow-button' href="Matching_input.php">திருமண பொருத்தம்</a>
				  <a class='glow-button' href="hora.php">ஓரை</a>
				  <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</a>
				  <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
				</div>
          </div> 
			<div class='menuHeading'>பஞ்சாங்கம்</div>
		</div>
	</div>

EOL;
}
```

### 3. indexMenu_mobile.php
**Location**: After BIRTHMATCH case
**Add**:
```php
elseif(strcmp($usertype,BIRTHPANCHMATCH)==0){
    echo <<<EOL
    <div class="menu-container">
        <div class="navbar">
          <div class="dropdown1">
            <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
            <div class="dropdown1-content">
              <a class='glow-button' href="Birth_input.php">பிறப்பு ஜாதகம்</a>
              <a class='glow-button' href="Matching_input.php">திருமண பொருத்தம்</a>
              <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
              <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
              <a class='glow-button' href="hora.php">ஓரை</a>
              <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</a>
            </div>
          </div> 
		  <a href="index.php"><i class="fa fa-home fa-2x"></i></a>
		  <div class='menuHeading'>முகப்பு</div>
        </div>
        </div>
EOL;
}
```

### 4. Other Menu Files That Need Similar Updates
Add BIRTHPANCHMATCH cases to:
- `birthInputMenu_mobile.php`
- `birthHistoryMenu_mobile.php`
- `matchingInputMenu_mobile.php`
- `matchingHistoryMenu_mobile.php`
- `horaMenu_mobile.php`
- `thaaraPalanMenu_mobile.php`
- `planetKaragamMenu_mobile.php`
- `helpMenu_mobile.php`

**Pattern**: Add after BIRTHPANCH cases, include all 3 features (Birth, Matching, Panchang)

### 5. Access Control Updates

#### index.php
**Find** (around line 74-77):
```php
elseif(strcmp($_SESSION['user_type'],BIRTH)==0 OR strcmp($_SESSION['user_type'],BIRTHPANCH)==0){
	header("Location: Birth_input.php");
}
elseif(strcmp($_SESSION['user_type'],MATCHING)==0 OR strcmp($_SESSION['user_type'],BIRTHMATCH)==0){
	header("Location: Matching_input.php");
}
```

**Update to**:
```php
elseif(strcmp($_SESSION['user_type'],BIRTH)==0 OR strcmp($_SESSION['user_type'],BIRTHPANCH)==0 OR strcmp($_SESSION['user_type'],BIRTHPANCHMATCH)==0){
	header("Location: Birth_input.php");
}
elseif(strcmp($_SESSION['user_type'],MATCHING)==0 OR strcmp($_SESSION['user_type'],BIRTHMATCH)==0 OR strcmp($_SESSION['user_type'],BIRTHPANCHMATCH)==0){
	header("Location: Matching_input.php");
}
```

#### Birth_input.php
Add BIRTHPANCHMATCH to access control checks:
```php
if(in_array($usertype, [BIRTH, BIRTHMATCH, BIRTHPANCH, BIRTHPANCHMATCH, SUPER, ADMIN])) {
    // Allow access
}
```

#### Matching_Chart.php / Matching_input.php
Add BIRTHPANCHMATCH to access control:
```php
if(in_array($usertype, [MATCHING, BIRTHMATCH, BIRTHPANCHMATCH, SUPER, ADMIN])) {
    // Allow access
}
```

#### new_panchang.php
Add BIRTHPANCHMATCH to access control:
```php
if(in_array($usertype, [PANCHANG, BIRTHPANCH, BIRTHPANCHMATCH, SUPER, ADMIN])) {
    // Allow access
}
```

### 6. Database/Registration Updates
- Update user registration forms to include BIRTHPANCHMATCH option
- Update admin.php user type dropdown to include BIRTHPANCHMATCH
- Update maintenance.php user type dropdown to include BIRTHPANCHMATCH

## Testing Checklist
- [ ] Create test user with BIRTHPANCHMATCH type
- [ ] Verify access to Birth Chart functionality
- [ ] Verify access to Matching functionality  
- [ ] Verify access to Panchang functionality
- [ ] Verify menus show all 3 feature links
- [ ] Verify proper navigation between sections
- [ ] Test that restricted features are blocked for other user types

## Notes
- BIRTHPANCHMATCH users should have ALL features from BIRTH + MATCHING + PANCHANG
- Menu dropdowns should show links to all 3 main features
- Access control should allow entry to all 3 feature areas
