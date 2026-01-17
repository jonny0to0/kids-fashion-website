<?php
/**
 * User Controller
 * Handles user authentication and profile management
 */

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Show login page
     */
    public function login()
    {
        if (Session::isLoggedIn()) {
            header('Location: ' . SITE_URL);
            exit;
        }

        // Check if there's a pending wishlist product
        if (isset($_GET['wishlist_product']) && !empty($_GET['wishlist_product'])) {
            $productId = (int) $_GET['wishlist_product'];
            Session::set('pending_wishlist_product_id', $productId);

            // Store redirect URL if provided
            if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                Session::set('redirect_after_login', $_GET['redirect']);
            } else {
                // Default redirect to product detail page
                $productModel = new Product();
                $product = $productModel->find($productId);
                if ($product) {
                    Session::set('redirect_after_login', SITE_URL . '/product/detail/' . $product['slug']);
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            $this->render('user/login');
        }
    }

    /**
     * Handle login request
     */
    private function handleLogin()
    {
        $email = Validator::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        $errors = [];

        if (!Validator::email($email)) {
            $errors[] = 'Please enter a valid email address';
        }

        if (empty($password)) {
            $errors[] = 'Please enter your password';
        }

        if (empty($errors)) {
            $user = $this->userModel->authenticate($email, $password);

            if ($user) {
                Session::set('user_id', $user['user_id']);
                Session::set('user_type', $user['user_type']);
                Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
                Session::set('user_email', $user['email']);

                if ($remember) {
                    // Set remember me cookie (implement secure token system)
                    setcookie('remember_token', base64_encode($user['user_id']), time() + (86400 * 30), '/');
                }

                // Handle pending wishlist product (only for customers)
                $wishlistAdded = false;
                if ($user['user_type'] !== USER_TYPE_ADMIN && Session::has('pending_wishlist_product_id')) {
                    $pendingProductId = Session::get('pending_wishlist_product_id');
                    Session::remove('pending_wishlist_product_id');

                    // Verify product exists
                    $productModel = new Product();
                    $product = $productModel->find($pendingProductId);

                    if ($product) {
                        $wishlistModel = new Wishlist();
                        // Add to wishlist if not already there
                        if (!$wishlistModel->isInWishlist($user['user_id'], $pendingProductId)) {
                            if ($wishlistModel->add($user['user_id'], $pendingProductId)) {
                                $wishlistAdded = true;
                            }
                        } else {
                            $wishlistAdded = true; // Already in wishlist, consider it successful
                        }
                    }
                }

                // Redirect admins to admin dashboard
                if ($user['user_type'] === USER_TYPE_ADMIN) {
                    $redirect = Session::get('redirect_after_login', SITE_URL . '/admin');
                } else {
                    $redirect = Session::get('redirect_after_login', SITE_URL);
                }
                Session::remove('redirect_after_login');

                if ($wishlistAdded) {
                    Session::setFlash('success', 'Welcome back! You have successfully logged in. The product has been added to your wishlist.');
                } else {
                    Session::setFlash('success', 'Welcome back! You have successfully logged in.');
                }
                header('Location: ' . $redirect);
                exit;
            } else {
                $errors[] = 'Invalid email or password';
            }
        }

        $this->render('user/login', ['errors' => $errors]);
    }

    /**
     * Show registration page
     */
    public function register()
    {
        if (Session::isLoggedIn()) {
            header('Location: ' . SITE_URL);
            exit;
        }

        // Check if there's a pending wishlist product (from login page redirect)
        if (Session::has('pending_wishlist_product_id')) {
            // Keep the pending wishlist product ID in session for after registration
            // Also preserve redirect URL if it exists
            if (!Session::has('redirect_after_login')) {
                $pendingProductId = Session::get('pending_wishlist_product_id');
                $productModel = new Product();
                $product = $productModel->find($pendingProductId);
                if ($product) {
                    Session::set('redirect_after_login', SITE_URL . '/product/detail/' . $product['slug']);
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
        } else {
            $this->render('user/register');
        }
    }

    /**
     * Handle registration request
     */
    private function handleRegister()
    {
        $data = [
            'first_name' => Validator::sanitize($_POST['first_name'] ?? ''),
            'last_name' => Validator::sanitize($_POST['last_name'] ?? ''),
            'email' => Validator::sanitize($_POST['email'] ?? ''),
            'phone' => Validator::sanitize($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ];

        $errors = [];

        // Validation
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }

        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }

        if (!Validator::email($data['email'])) {
            $errors[] = 'Please enter a valid email address';
        } elseif ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'Email address is already registered';
        }

        if (!empty($data['phone']) && !Validator::phone($data['phone'])) {
            $errors[] = 'Please enter a valid phone number';
        }

        if (!Validator::password($data['password'])) {
            $errors[] = 'Password must be at least 8 characters with letters and numbers';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Passwords do not match';
        }

        if (empty($errors)) {
            unset($data['confirm_password']);
            $userId = $this->userModel->register($data);

            if ($userId) {
                // Auto-login after registration
                $user = $this->userModel->find($userId);
                if ($user) {
                    Session::set('user_id', $user['user_id']);
                    Session::set('user_type', $user['user_type']);
                    Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
                    Session::set('user_email', $user['email']);

                    // Handle pending wishlist product (only for customers)
                    $wishlistAdded = false;
                    if ($user['user_type'] !== USER_TYPE_ADMIN && Session::has('pending_wishlist_product_id')) {
                        $pendingProductId = Session::get('pending_wishlist_product_id');
                        Session::remove('pending_wishlist_product_id');

                        // Verify product exists
                        $productModel = new Product();
                        $product = $productModel->find($pendingProductId);

                        if ($product) {
                            $wishlistModel = new Wishlist();
                            // Add to wishlist if not already there
                            if (!$wishlistModel->isInWishlist($user['user_id'], $pendingProductId)) {
                                if ($wishlistModel->add($user['user_id'], $pendingProductId)) {
                                    $wishlistAdded = true;
                                }
                            } else {
                                $wishlistAdded = true; // Already in wishlist, consider it successful
                            }
                        }
                    }

                    // Get redirect URL
                    $redirect = Session::get('redirect_after_login', SITE_URL);
                    Session::remove('redirect_after_login');

                    if ($wishlistAdded) {
                        Session::setFlash('success', 'Registration successful! The product has been added to your wishlist.');
                    } else {
                        Session::setFlash('success', 'Registration successful! Welcome to our store.');
                    }
                    header('Location: ' . $redirect);
                    exit;
                } else {
                    Session::setFlash('success', 'Registration successful! Please login.');
                    header('Location: ' . SITE_URL . '/user/login');
                    exit;
                }
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }

        $this->render('user/register', ['errors' => $errors, 'data' => $data]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        Session::destroy();
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        header('Location: ' . SITE_URL);
        exit;
    }

    /**
     * User profile
     */
    public function profile()
    {
        $this->requireAuth();

        $userId = Session::getUserId();
        $user = $this->userModel->find($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProfileUpdate($userId);
        } else {
            $this->render('user/profile', ['user' => $user]);
        }
    }

    /**
     * Handle profile update
     */
    private function handleProfileUpdate($userId)
    {
        $data = [
            'first_name' => Validator::sanitize($_POST['first_name'] ?? ''),
            'last_name' => Validator::sanitize($_POST['last_name'] ?? ''),
            'phone' => Validator::sanitize($_POST['phone'] ?? '')
        ];

        if ($this->userModel->updateProfile($userId, $data)) {
            // Update session name
            Session::set('user_name', $data['first_name'] . ' ' . $data['last_name']);
            Session::setFlash('success', 'Profile updated successfully');
            header('Location: ' . SITE_URL . '/user/profile');
            exit;
        }
    }

    /**
     * User wishlist
     */
    public function wishlist()
    {
        $this->requireAuth();

        // Restrict access to customers only
        if (Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. This feature is only available for customers.');
            header('Location: ' . SITE_URL . '/admin');
            exit;
        }

        $userId = Session::getUserId();
        $wishlistModel = new Wishlist();
        $wishlistItems = $wishlistModel->getUserWishlist($userId);

        $this->render('user/wishlist', ['wishlistItems' => $wishlistItems]);
    }

    /**
     * Add item to wishlist (AJAX)
     */
    public function wishlistAdd()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        // Check if this is just an auth check (from JavaScript)
        $checkAuth = isset($_POST['check_auth']) && $_POST['check_auth'] === '1';

        // If user is not logged in, return requires_auth flag
        if (!Session::isLoggedIn()) {
            if ($checkAuth) {
                echo json_encode(['success' => false, 'requires_auth' => true, 'message' => 'Please login to add items to wishlist']);
            } else {
                echo json_encode(['success' => false, 'requires_auth' => true, 'message' => 'Please login to add items to wishlist']);
            }
            exit;
        }

        // Restrict access to customers only
        if (Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Access denied. This feature is only available for customers.']);
            exit;
        }

        $userId = Session::getUserId();
        $productId = (int) ($_POST['product_id'] ?? 0);

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        // Verify product exists
        $productModel = new Product();
        $product = $productModel->find($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        $wishlistModel = new Wishlist();

        // Check if already in wishlist
        if ($wishlistModel->isInWishlist($userId, $productId)) {
            // Get current wishlist count
            $count = $wishlistModel->getCount($userId);
            echo json_encode(['success' => true, 'message' => 'Item already in wishlist', 'in_wishlist' => true, 'count' => $count]);
            exit;
        }

        if ($wishlistModel->add($userId, $productId)) {
            // Get updated wishlist count
            $count = $wishlistModel->getCount($userId);
            echo json_encode(['success' => true, 'message' => 'Item added to wishlist', 'in_wishlist' => true, 'count' => $count]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add item to wishlist. Please try again.']);
        }
        exit;
    }

    /**
     * Remove item from wishlist (AJAX)
     */
    public function wishlistRemove()
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        // Restrict access to customers only
        if (Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Access denied. This feature is only available for customers.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $userId = Session::getUserId();
        $productId = (int) ($_POST['product_id'] ?? 0);

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        $wishlistModel = new Wishlist();

        if ($wishlistModel->remove($userId, $productId)) {
            // Get updated wishlist count
            $count = $wishlistModel->getCount($userId);
            echo json_encode(['success' => true, 'message' => 'Item removed from wishlist', 'in_wishlist' => false, 'count' => $count]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item from wishlist. It may not be in your wishlist.']);
        }
        exit;
    }

    /**
     * Get wishlist count (AJAX)
     */
    public function wishlistGetCount()
    {
        // Only allow AJAX requests
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Also check if it's a fetch request (modern browsers)
        $isFetch = !empty($_SERVER['HTTP_ACCEPT']) &&
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        if (!$isAjax && !$isFetch) {
            // Not an AJAX request, redirect to home
            header('Location: ' . SITE_URL);
            exit;
        }

        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            echo json_encode(['success' => false, 'count' => 0]);
            exit;
        }
        header('Content-Type: application/json');

        // Restrict access to customers only
        if (Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Access denied. This feature is only available for customers.', 'count' => 0]);
            exit;
        }

        $userId = Session::getUserId();
        $wishlistModel = new Wishlist();
        $count = $wishlistModel->getCount($userId);

        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        exit;
    }

    /**
     * Add address (AJAX endpoint)
     */
    public function addressAdd()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAddressSave();
        } else {
            // Return JSON for AJAX requests
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'form' => $this->getAddressFormHtml()]);
        }
    }

    /**
     * Save address
     */
    private function handleAddressSave()
    {
        $userId = Session::getUserId();
        $addressModel = new Address();

        $data = [
            'user_id' => $userId,
            'address_type' => Validator::sanitize($_POST['address_type'] ?? 'home'),
            'full_name' => Validator::sanitize($_POST['full_name'] ?? ''),
            'phone' => Validator::sanitize($_POST['phone'] ?? ''),
            'address_line1' => Validator::sanitize($_POST['address_line1'] ?? ''),
            'address_line2' => '', // Address Line 2 removed, set to empty string
            'city' => Validator::sanitize($_POST['city'] ?? ''),
            'state' => Validator::sanitize($_POST['state'] ?? ''),
            'pincode' => Validator::sanitize($_POST['pincode'] ?? ''),
            'country' => Validator::sanitize($_POST['country'] ?? 'India'),
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];

        $errors = [];

        // Validation
        if (empty($data['full_name'])) {
            $errors[] = 'Full name is required';
        }

        if (empty($data['phone'])) {
            $errors[] = 'Phone number is required';
        } elseif (!Validator::phone($data['phone'])) {
            $errors[] = 'Please enter a valid phone number';
        }

        if (empty($data['address_line1'])) {
            $errors[] = 'Address line 1 is required';
        }

        if (empty($data['city'])) {
            $errors[] = 'City is required';
        }

        if (empty($data['state'])) {
            $errors[] = 'State is required';
        }

        if (empty($data['pincode'])) {
            $errors[] = 'Pincode is required';
        }

        header('Content-Type: application/json');

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // If this is the first address or marked as default, set it as default
        $existingAddresses = $addressModel->getUserAddresses($userId);
        if (empty($existingAddresses) || $data['is_default']) {
            $data['is_default'] = 1;
            // Remove default from other addresses
            $addressModel->query("UPDATE addresses SET is_default = 0 WHERE user_id = ?", [$userId]);
        }

        $addressId = $addressModel->create($data);

        if ($addressId) {
            echo json_encode([
                'success' => true,
                'message' => 'Address added successfully',
                'address_id' => $addressId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save address. Please try again.']);
        }
    }

    /**
     * Get address form HTML
     */
    private function getAddressFormHtml()
    {
        // Get user profile data to pre-populate form
        $userId = Session::getUserId();
        $user = $this->userModel->find($userId);

        // Combine first name and last name for full name
        $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $phone = $user['phone'] ?? '';

        ob_start();
        ?>
        <form id="address-form" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" required value="<?php echo htmlspecialchars($fullName); ?>"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="Enter your full name">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Phone Number <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" required value="<?php echo htmlspecialchars($phone); ?>"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="10-digit mobile number">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Address Type</label>
                <select name="address_type"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <option value="home">Home</option>
                    <option value="work">Work</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Address Line 1 <span class="text-red-500">*</span></label>
                <input type="text" name="address_line1" required
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="House/Flat No., Building Name">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">City <span class="text-red-500">*</span></label>
                    <input type="text" name="city" required
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="City">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">State <span class="text-red-500">*</span></label>
                    <input type="text" name="state" required
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="State">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Pincode <span class="text-red-500">*</span></label>
                    <input type="text" name="pincode" required pattern="[0-9]{6}"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="6-digit pincode">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Country</label>
                    <input type="text" name="country" value="India" readonly
                        class="w-full border border-gray-300 rounded px-4 py-2 bg-gray-100">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_default" id="is_default" class="mr-2">
                <label for="is_default" class="text-gray-700">Set as default address</label>
            </div>

            <div id="address-form-errors" class="text-red-600 text-sm hidden"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Require authentication
     */
    private function requireAuth()
    {
        if (!Session::isLoggedIn()) {
            Session::set('redirect_after_login', $_SERVER['REQUEST_URI']);
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }
    }

    /**
     * Render view
     */
    private function render($view, $data = [])
    {
        extract($data);
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/' . $view . '.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }
}

