<?php

function getAllLlms($conn) {
    $result = mysqli_query($conn, "
        SELECT id, name, description, url, logo
        FROM llm
        ORDER BY name ASC
    ");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getLlmById($conn, $id) {
    $id = (int) $id;
    $result = mysqli_query($conn, "
        SELECT id, name, description, url, logo
        FROM llm
        WHERE id = $id
    ");
    return mysqli_fetch_assoc($result);
}

function getLlmsWithRating($conn, array $tag_ids = []) {
    $where = '';
    if (!empty($tag_ids)) {
        $ids   = implode(',', $tag_ids);
        $count = count($tag_ids);
        $where = "WHERE l.id IN (
            SELECT tl.llm_id FROM tag_llm tl
            WHERE tl.tag_id IN ($ids)
            GROUP BY tl.llm_id
            HAVING COUNT(DISTINCT tl.tag_id) = $count
        )";
    }
    $result = mysqli_query($conn, "
        SELECT l.id, l.name, l.description, l.url, l.logo, l.pricing,
               COALESCE(ROUND(AVG(r.stars), 1), 0) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM llm l
        LEFT JOIN review r ON l.id = r.llm_id
        $where
        GROUP BY l.id, l.name, l.description, l.url, l.logo, l.pricing
        ORDER BY l.name ASC
    ");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getTagLlmMap($conn) {
    $result = mysqli_query($conn, "SELECT tag_id, llm_id FROM tag_llm");
    $map = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $map[(int)$row['tag_id']][] = (int)$row['llm_id'];
    }
    return $map;
}

function getAllTags($conn) {
    $result = mysqli_query($conn, "
        SELECT t.id, t.name
        FROM tag t
        WHERE EXISTS (SELECT 1 FROM tag_llm tl WHERE tl.tag_id = t.id)
        ORDER BY t.name ASC
    ");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getTagsForLlm($conn, $llm_id) {
    $llm_id = (int) $llm_id;
    $result = mysqli_query($conn, "
        SELECT t.id, t.name
        FROM tag t
        JOIN tag_llm tl ON t.id = tl.tag_id
        WHERE tl.llm_id = $llm_id
        ORDER BY t.name ASC
    ");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getReviewsForLlm($conn, $llm_id) {
    $llm_id = (int) $llm_id;
    $result = mysqli_query($conn, "
        SELECT id, stars, created_at
        FROM review
        WHERE llm_id = $llm_id
        ORDER BY created_at DESC
    ");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getAvgRatingForLlm($conn, $llm_id) {
    $llm_id = (int) $llm_id;
    $result = mysqli_query($conn, "
        SELECT COALESCE(ROUND(AVG(stars), 1), 0) AS avg_rating,
               COUNT(id) AS review_count
        FROM review
        WHERE llm_id = $llm_id
    ");
    return mysqli_fetch_assoc($result);
}

function saveReview($conn, $llm_id, $stars) {
    if (empty($stars)) {
        return 'Vyber prosím počet hvězdiček.';
    }

    $stars = (int) $stars;
    if ($stars < 1 || $stars > 5) {
        return 'Hodnocení musí být mezi 1 a 5.';
    }

    $llm_id = (int) $llm_id;

    if (mysqli_query($conn, "INSERT INTO review (llm_id, stars) VALUES ($llm_id, $stars)")) {
        return true;
    }
    return 'Chyba při ukládání: ' . mysqli_error($conn);
}

function getAllNews($conn) {
    $result = mysqli_query($conn, "
        SELECT id, title, content, created_at
        FROM news
        ORDER BY created_at DESC
    ");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function renderHvezdicky($pocet) {
    $pocet  = (int) $pocet;
    $vystup = '';
    for ($i = 1; $i <= 5; $i++) {
        $vystup .= ($i <= $pocet) ? '★' : '☆';
    }
    return $vystup;
}
