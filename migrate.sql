-- ════════════════════════════════════════════
--  ExplorAI – DB migrace
--  Spusť v phpMyAdmin nebo MySQL CLI
-- ════════════════════════════════════════════

-- Tabulka novinek (pokud ještě neexistuje)
CREATE TABLE IF NOT EXISTS news (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(255) NOT NULL,
    content    TEXT         NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ukázková data novinek
INSERT INTO news (title, content) VALUES
('GPT-4o – nová verze ChatGPT', 'OpenAI vydalo GPT-4o, výrazně rychlejší model s podporou zpracování textu, obrázků a audia v reálném čase. Nová verze je dostupná zdarma pro všechny uživatele ChatGPT.'),
('Google Gemini 2.0 Flash', 'Google představilo Gemini 2.0 Flash – model zaměřený na rychlost a nízkou latenci. Podporuje multimodální vstupy včetně videa a je dostupný přes Google AI Studio.'),
('Claude 3.5 Sonnet od Anthropic', 'Anthropic vydalo Claude 3.5 Sonnet, model s vynikajícími výsledky v kódování a analýze. Nabízí rozšířené kontextové okno a je dostupný přes API i na claude.ai.'),
('Midjourney V7 – revoluce v generování obrazu', 'Midjourney vydalo verzi 7 svého generátoru obrázků. Přináší výrazně realističtější výstupy, lepší pochopení textu v promptu a nový webový interface s historií obrázků.'),
('Perplexity AI – nová funkce Spaces', 'Perplexity AI přidalo funkci Spaces, která umožňuje sdílet průzkumy a výsledky hledání s týmem. Nová verze také zlepšuje citace a přesnost zdrojů.');

-- ════════════════════════════════════════════
--  Ukázková struktura tabulek (jen info)
-- ════════════════════════════════════════════
--
-- Tabulka llm:
--   id INT AI PK, name VARCHAR(255), description TEXT,
--   url VARCHAR(512), logo VARCHAR(255)
--
-- Tabulka tag:
--   id INT AI PK, name VARCHAR(100)
--
-- Tabulka tag_llm:
--   tag_id INT, llm_id INT
--
-- Tabulka review:
--   id INT AI PK, llm_id INT, stars TINYINT(1), created_at TIMESTAMP DEFAULT NOW()
