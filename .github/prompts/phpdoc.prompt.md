---
name: "phpdoc"
description: "Generate or update PHPDoc comments"
agent: "agent"
---

# Update PHPDoc Docblocks

Review and update all docblocks in this file to strictly adhere to PHPDoc format and Drupal Coding Standards. Do not modify any actual code - only update the docblock comments.

Ensure that:

1. All docblocks have proper summary lines (short, concise, ending with period)
2. All `@param` tags include type hints and clear descriptions
3. All methods have `@return` tags with type hints and descriptions if a return type is not already provided by the function or method being documented.
4. Only include `@throws` tags for exceptions actually thrown by the method (not inherited/bubbled up unless documented in Drupal standards)
5. Use proper type formatting (e.g., `int[]`, `string[]`, `\Drupal\node\Entity\Node`, etc.)
6. Descriptions use proper grammar and are clear and helpful
7. Class docblocks provide context about the class's purpose
8. Private/protected method docblocks are as thorough as public ones
9. Use `@inheritdoc` whenever possible

**Important: Preserve all existing code exactly as-is. Only modify the docblock comments.**
