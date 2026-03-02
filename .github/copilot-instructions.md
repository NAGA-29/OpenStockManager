## AI Assistant

- Respond in Japanese
- サマリーの作成時も日本語で回答してください
- When providing code examples, use code blocks

## Project Overview

- Purpose: 在庫管理システムのバックエンド
  - 在庫情報の登録・更新・参照などのAPIを提供するバックエンドサービス
  - 認証・認可や監査ログなど、在庫管理に必要な機能を提供する

## Development Methodology

- Based on Test-Driven Development (TDD), covering unit tests, integration tests, and end-to-end tests

## Coding Guidelines

- Use PHPDoc to create documentation for functions and classes
- Implement linters and code formatters to run automatically when new code is added or modified
- Create TODO.md files to clarify required tasks. Check off completed items to maintain a record
- Follow the DRY principle
- Follow the SOLID principles
- Follow the KISS principle
- Follow the YAGNI principle
- Never neglect updating documentation when modifying code
- Write unit tests for business logic
- Avoid writing code that contains vulnerabilities
- Consider scalability

## Prohibited Actions

- Do not commit code without tests
- Do not expose sensitive information (API keys, passwords) in code
- Do not modify existing behavior without updating related tests
- Do not ignore linter/formatter warnings
- Do not use magic numbers (use named constants instead)

## Contribution Checklist

- Favor pure functions.
- When finished, generate a one liner commit message summarizing changes

## Commands I use frequently

- If work is performed, reflect the relevant sections in the internal specifications (under Docs/sow/)