# Button Component

## Overview
The Button component is a reusable UI element that provides consistent styling and behavior across the application.

## Usage

### Basic Usage
```blade
<x-button>Click me</x-button>
```

### With Attributes
```blade
<x-button 
    type="submit" 
    variant="primary" 
    size="lg"
    :disabled="false"
    :loading="false"
>
    Submit Form
</x-button>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `type` | string | 'button' | HTML button type (button, submit, reset) |
| `variant` | string | 'primary' | Button style variant |
| `size` | string | 'md' | Button size |
| `disabled` | boolean | false | Whether the button is disabled |
| `loading` | boolean | false | Whether the button is in loading state |

## Variants

- `primary` - Blue background, white text
- `secondary` - Gray background, white text
- `success` - Green background, white text
- `danger` - Red background, white text
- `warning` - Yellow background, white text
- `info` - Cyan background, white text
- `outline` - White background, gray border
- `ghost` - Transparent background, gray text

## Sizes

- `xs` - Extra small (px-2.5 py-1.5 text-xs)
- `sm` - Small (px-3 py-2 text-sm)
- `md` - Medium (px-4 py-2 text-sm)
- `lg` - Large (px-4 py-2 text-base)
- `xl` - Extra large (px-6 py-3 text-base)

## Examples

### Form Submit Button
```blade
<x-button type="submit" variant="primary" size="lg">
    Save Changes
</x-button>
```

### Delete Button
```blade
<x-button variant="danger" size="sm">
    Delete
</x-button>
```

### Loading Button
```blade
<x-button :loading="true" variant="primary">
    Processing...
</x-button>
```

### Disabled Button
```blade
<x-button :disabled="true" variant="secondary">
    Not Available
</x-button>
```

## Styling

The component uses Tailwind CSS classes and follows the design system. All variants and sizes are responsive and accessible.

## Accessibility

- Proper ARIA attributes
- Keyboard navigation support
- Screen reader friendly
- Focus indicators

## JavaScript Integration

The component works with Alpine.js for interactive features:

```blade
<x-button @click="handleClick" :loading="isLoading">
    Click me
</x-button>
```
