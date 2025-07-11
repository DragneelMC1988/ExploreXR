# ExploreXR Coding Standards

This document outlines the coding standards for the ExploreXR project. Following these guidelines ensures consistency throughout the codebase and makes it easier for contributors to work together.

## PHP Coding Standards

### General

- Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Use PHP 7.4+ compatible syntax
- UTF-8 encoding for all PHP files
- Line endings should be LF (Unix style)
- Indentation: 4 spaces (no tabs)
- Maximum line length: 100 characters
- End files with a single newline
- No closing PHP tag in files containing only PHP

### Naming Conventions

- Functions: `snake_case` with plugin prefix
  - Example: `explorexr_process_model()`
- Classes: `PascalCase` with plugin prefix
  - Example: `ExploreXR_Model_Manager`
- Constants: `UPPERCASE_WITH_UNDERSCORES` with plugin prefix
  - Example: `EXPLOREXR_VERSION`
- Variables: `snake_case`
  - Example: `$model_data`, `$upload_path`
- Private/protected methods: `snake_case` with underscore prefix
  - Example: `_process_file()`
- Database table names: lowercase with underscores and plugin prefix
  - Example: `{$wpdb->prefix}explorexr_models`
- Option names: lowercase with underscores and plugin prefix
  - Example: `explorexr_settings`, `explorexr_model_options`
  
### Documentation

- All functions, classes, and methods should have PHPDoc blocks
- Follow WordPress PHPDoc standards
- Document parameters, return values, and exceptions
- Include descriptions that explain "why" not just "what"

Example:
```php
/**
 * Process a 3D model file upload and create associated data.
 *
 * Takes an uploaded file, validates it, moves it to the appropriate
 * storage location, and creates a post for the model with relevant metadata.
 *
 * @since 1.0.0
 *
 * @param array $file           The uploaded file data from $_FILES.
 * @param array $model_data     Associated model data including title and description.
 * @param int   $user_id        The user ID who is uploading the model.
 * @return int|WP_Error         The model post ID on success, WP_Error on failure.
 */
function explorexr_process_model_upload( $file, $model_data, $user_id ) {
    // Function implementation
}
```

### Security

- Always sanitize inputs using appropriate WordPress functions
- Always escape outputs using appropriate WordPress functions
- Always validate capabilities before performing privileged actions
- Use nonces for forms and AJAX requests
- Sanitize file uploads and validate file types rigorously

## JavaScript Coding Standards

### General

- Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- Use ES6+ syntax with appropriate polyfills
- Indentation: 2 spaces (no tabs)
- Maximum line length: 80 characters
- End files with a single newline
- Use semicolons to terminate statements

### Naming Conventions

- Functions and variables: `camelCase`
- Classes: `PascalCase`
- Constants: `UPPERCASE_WITH_UNDERSCORES`
- Private methods/properties: `_prefixWithUnderscore`
- File names: `kebab-case.js`
- Component names: `PascalCase`

### Organization

- Group related functionality into modules
- Use namespacing to prevent global pollution
- Initialize all variables at the top of their scope
- Comment complex logic and edge cases

Example:
```javascript
/**
 * Handles model viewer initialization and events.
 * 
 * @since 1.0.0
 */
ExploreXR.ModelViewer = class {
  /**
   * Create a new model viewer instance.
   *
   * @param {HTMLElement} container - The container element.
   * @param {Object} options - Configuration options.
   */
  constructor(container, options = {}) {
    this.container = container;
    this.options = this._mergeDefaults(options);
    this.isLoaded = false;
    
    this._initialize();
  }
  
  /**
   * Initialize the model viewer.
   *
   * @private
   */
  _initialize() {
    // Implementation
  }
};
```

## CSS Coding Standards

### General

- Follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
- Use [BEM methodology](http://getbem.com/naming/) for class naming
- Indentation: 2 spaces (no tabs)
- Use kebab-case for class names
- Group related properties and follow logical property order
- Comment sections and complex styling

### Naming Conventions

- Block: `explorexr-block-name`
- Element: `explorexr-block-name__element-name`
- Modifier: `explorexr-block-name--modifier-name`
- State: `is-state-name` (e.g., `is-active`, `is-disabled`)

### Organization

- Group related styles
- Use comments to separate sections
- Place media queries at the end of the related rule
- Avoid unnecessary nesting
- Limit specificity

Example:
```css
/**
 * Model viewer container and controls.
 */
.explorexr-model-viewer {
  position: relative;
  width: 100%;
  height: 400px;
  background-color: #f8f8f8;
  overflow: hidden;
}

.explorexr-model-viewer__controls {
  position: absolute;
  bottom: 10px;
  left: 10px;
  z-index: 10;
  display: flex;
  gap: 8px;
}

.explorexr-model-viewer__button {
  padding: 6px 10px;
  background-color: rgba(255, 255, 255, 0.8);
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.explorexr-model-viewer__button--primary {
  background-color: #0073aa;
  color: white;
}

.explorexr-model-viewer__button.is-active {
  outline: 2px solid #0073aa;
}

/* Responsive styles */
@media (max-width: 768px) {
  .explorexr-model-viewer {
    height: 300px;
  }
  
  .explorexr-model-viewer__controls {
    bottom: 5px;
    left: 5px;
  }
}
```

## File Structure Standards

### PHP Files

- One class per file
- File name should match the class name with lowercase and hyphens
- Group related functionality in directories
- Follow WordPress file organization patterns

### JavaScript Files

- Group functionality by feature
- Follow modular pattern
- Use consistent file naming conventions
- Keep files focused on a single responsibility

### CSS Files

- Separate files for admin and front-end styles
- Group related styles in the same file
- Keep files reasonably sized
- Use consistent file naming conventions

## Version Control Practices

### Commit Messages

- Use present tense ("Add feature" not "Added feature")
- First line should be a summary (50 chars or less)
- Include references to issue numbers when applicable
- Be descriptive about what and why, not how

Format:
```
type: Short summary under 50 chars

More detailed explanatory text if needed. Wrap at around 72 chars.
Explain the problem that this commit is solving and why, not how.

Fixes #123
```

Where `type` is one of:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Formatting, missing semi colons, etc; no code change
- `refactor`: Refactoring production code
- `test`: Adding tests, refactoring tests; no production code change
- `chore`: Updating build tasks, package manager configs, etc; no production code change

### Branching Strategy

- `main`: Production-ready code
- `develop`: Development branch for next release
- `feature/name`: New features
- `fix/name`: Bug fixes
- `release/x.x.x`: Release preparation
- `hotfix/name`: Urgent fixes for production

## Testing Standards

### PHP Testing

- Use PHPUnit for unit and integration tests
- Follow WordPress testing practices
- Test all public methods
- Mock external dependencies

### JavaScript Testing

- Use Jest for unit tests
- Test components and utility functions
- Mock external dependencies and API calls

## Accessibility Standards

- Follow [WCAG 2.1 AA](https://www.w3.org/WAI/WCAG21/quickref/) standards
- Ensure keyboard navigation works properly
- Use proper ARIA attributes
- Test with screen readers
- Maintain sufficient color contrast
- Provide text alternatives for non-text content

## Internationalization Standards

- Make all user-facing strings translatable
- Use `__()`, `_e()`, etc., from WordPress i18n functions
- Provide context with `_x()` when necessary
- Use proper text domains
- Support RTL languages

Example:
```php
printf(
    /* translators: %s: model name */
    __('The model "%s" has been uploaded successfully.', 'explorexr'),
    esc_html($model_name)
);
```

## Documentation Standards

- Keep README.md updated
- Document hooks and filters
- Provide code examples for developers
- Update changelog with every release
- Include usage instructions for end users

---

These coding standards are guidelines to help maintain quality and consistency. They may evolve over time as best practices change.

Last updated: July 2025
