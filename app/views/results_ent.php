<?php
namespace Transvision;

// rtl support
$direction1 = RTLSupport::getDirection($source_locale);
$direction2 = RTLSupport::getDirection($locale);

if ($url['path'] == '3locales') {
    $direction3 = RTLSupport::getDirection($locale2);
    $extra_column_header = "<th>{$locale2}</th>";
} else {
    $extra_column_header = '';
}

$table  = "<table class='collapsable'>
              <tr>
                <th>Entity</th>
                <th>{$source_locale}</th>
                <th>{$locale}</th>
                {$extra_column_header}
              </tr>";

// Display a search hint for the closest string we have if we have no search results
if (count($entities) == 0) {
    $merged_strings = [];

    $best_matches = Strings::getSimilar($initial_search, array_keys($tmx_source), 3);

    $proposed_search = $_GET;

    print "<p>We couldn't find this entity, did you mean:</p>";
    print '<ul>';

    foreach ($best_matches as $match) {
        $proposed_search['recherche'] = $match;
        $query = '?' .http_build_query($proposed_search, '', '&amp;');

        print '<li><a href="' . $query . '">' . $match . '</a></li>';
    }

    print '</ul>';
    return;
}

foreach ($entities as $entity) {
    if ($check['repo'] == 'mozilla_org') {
        $path_locale1 = VersionControl::svnPath($source_locale, $check['repo'], $entity);
        $path_locale2 = VersionControl::svnPath($locale, $check['repo'], $entity);
    } else {
        $path_locale1 = VersionControl::hgPath($source_locale, $check['repo'], $entity);
        $path_locale2 = VersionControl::hgPath($locale, $check['repo'], $entity);
    }

    if ($url['path'] == '3locales') {

        if (isset($tmx_target2[$entity])) {
            // nbsp highlight
            $target_string2 = str_replace(' ', '<span class="highlight-gray"> </span>', $tmx_target2[$entity]);
        } else {
            $target_string2 = '';
        }

        if ($check['repo'] == 'mozilla_org') {
            $path_locale3 = VersionControl::svnPath($locale2, $check['repo'], $entity);
        } else {
            $path_locale3 = VersionControl::hgPath($locale2, $check['repo'], $entity);
        }

    }

    if (isset($tmx_target[$entity])) {
        // nbsp highlight
        $target_string = str_replace(' ', '<span class="highlight-gray"> </span>', $tmx_target[$entity]);
    } else {
        $target_string = '';
    }

    $source_string = $tmx_source[$entity];

    // 3locales view
    if ($url['path'] == '3locales') {
        // Link to entity
        $entity_link = "?sourcelocale={$source_locale}"
                     . "&locale={$locale2}"
                     . "&repo={$check['repo']}"
                     . "&search_type=entities&recherche={$entity}";

        $file_bug = '<a class="bug_link" target="_blank" href="'
                    . Bugzilla::reportErrorLink($locale2, $entity, $source_string,
                                              $target_string2, $check['repo'], $entity_link)
                  . '">&lt;report a bug&gt;</a>';

        $extra_column_rows = "
        <td dir='{$direction3}'>
            <span class='celltitle'>{$locale3}</span>
            <div class='string'>{$target_string2}</div>
            <div dir='ltr' class='infos'>
              <a class='source_link' href='{$path_locale3}'>
                <em>&lt;source&gt;</em>
              </a>
              {$file_bug}
            </div>
          </td>
        </tr>";

    } else {
        $extra_column_rows = '';
    }

    // Link to entity
    $entity_link = "?sourcelocale={$source_locale}"
                 . "&locale={$locale}"
                 . "&repo={$check['repo']}"
                 . "&search_type=entities&recherche={$entity}";

    $file_bug = '<a class="bug_link" target="_blank" href="'
                . Bugzilla::reportErrorLink($locale, $entity, $source_string,
                                          $target_string, $check['repo'], $entity_link)
              . '">&lt;report a bug&gt;</a>';
    $anchor_name = str_replace(array('/', ':'), '_', $entity);
    $table .= "<tr>
                    <td>
                    <span class='celltitle'>Entity</span>
                    <a class='resultpermalink tag' id='{$anchor_name}' href='#{$anchor_name}' title='Permalink to this result'>link</a>
                    <a class='l10n tag' href='/string/?entity={$entity}&amp;repo={$check['repo']}' title='List all translations for this entity'>l10n</a>
                    <a class='linktoentity' href='/{$entity_link}'>" . ShowResults::formatEntity($entity, $my_search) . "</a></td>
                    <td dir='{$direction1}'>
                       <span class='celltitle'>{$source_locale}</span>
                       <div class='string'>{$source_string}</div>
                       <div dir='ltr' class='infos'>
                        <a class='source_link' href='{$path_locale1}'><em>&lt;source&gt;</em></a>
                       </div>
                    </td>
                     <td dir='{$direction2}'>
                       <span class='celltitle'>{$locale}</span>
                       <div class='string'>{$target_string}</div>
                       <div dir='ltr' class='infos'>
                        <a class='source_link' href='{$path_locale2}'><em>&lt;source&gt;</em></a>
                        {$file_bug}
                       </div>
                    </td>
                {$extra_column_rows}
                </tr>";
}

$table .= "  </table>\n\n";

print $table;
