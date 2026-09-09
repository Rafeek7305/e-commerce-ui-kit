jQuery(document).ready(function($) {
    const $input = $('#handzom_content_json');
    const $ui = $('#handzom-content-ui');
    
    if (!$input.length || !$ui.length) return;

    let data = { page_title: '', sections: [] };
    
    try {
        data = JSON.parse($input.val());
    } catch (e) {
        console.error("Invalid JSON data", e);
    }
    
    if (typeof data !== 'object' || !data) data = { page_title: '', sections: [] };
    if (!Array.isArray(data.sections)) data.sections = [];

    function render() {
        $ui.empty();
        
        // Page Title
        const $pageTitleContainer = $('<div class="hz-field hz-page-title-field"></div>');
        $pageTitleContainer.append('<label>Page Title</label>');
        const $pageTitleInput = $('<input type="text" class="widefat" placeholder="Page Title" />').val(data.page_title);
        $pageTitleInput.on('input', function() {
            data.page_title = $(this).val();
            updateInput();
        });
        $pageTitleContainer.append($pageTitleInput);
        $ui.append($pageTitleContainer);

        const $sectionsContainer = $('<div class="hz-sections"></div>');
        
        data.sections.forEach((section, sIndex) => {
            const $sectionWrapper = $('<div class="hz-section-wrapper" data-index="'+sIndex+'"></div>');
            const $sectionHeader = $('<div class="hz-section-header"></div>');
            $sectionHeader.append('<h4>Section ' + (sIndex + 1) + '</h4>');
            
            const $sectionActions = $('<div class="hz-actions"></div>');
            const $toggleBtn = $('<button type="button" class="button button-small hz-toggle">+</button>');
            const $deleteBtn = $('<button type="button" class="button button-small button-link-delete">Delete</button>');
            
            $sectionActions.append($toggleBtn).append($deleteBtn);
            $sectionHeader.append($sectionActions);
            
            const $sectionBody = $('<div class="hz-section-body" style="display: none;"></div>');
            
            // Section Title
            const $sectionTitle = $('<div class="hz-field"><label>Section Title</label><input type="text" class="widefat" placeholder="Section Title" value="'+(section.title||'')+'" /></div>');
            $sectionTitle.find('input').on('input', function() {
                data.sections[sIndex].title = $(this).val();
                updateInput();
            });
            $sectionBody.append($sectionTitle);
            
            // Subtitles
            const $subtitlesContainer = $('<div class="hz-subtitles"></div>');
            if (!Array.isArray(section.subtitles)) section.subtitles = [];
            
            section.subtitles.forEach((subtitle, subIndex) => {
                const $subWrapper = $('<div class="hz-subtitle-wrapper" data-index="'+subIndex+'"></div>');
                const $subHeader = $('<div class="hz-subtitle-header"></div>');
                $subHeader.append('<h5>Sub Title ' + (subIndex + 1) + '</h5>');
                
                const $subActions = $('<div class="hz-actions"></div>');
                const $subToggleBtn = $('<button type="button" class="button button-small hz-toggle">+</button>');
                const $subDeleteBtn = $('<button type="button" class="button button-small button-link-delete">Delete</button>');
                
                $subActions.append($subToggleBtn).append($subDeleteBtn);
                $subHeader.append($subActions);
                
                const $subBody = $('<div class="hz-subtitle-body" style="display: none;"></div>');
                
                const $subTitleField = $('<div class="hz-field"><label>Subtitle</label><input type="text" class="widefat" placeholder="Subtitle" value="'+(subtitle.title||'')+'" /></div>');
                $subTitleField.find('input').on('input', function() {
                    data.sections[sIndex].subtitles[subIndex].title = $(this).val();
                    updateInput();
                });
                
                const $subTypeField = $('<div class="hz-field"><label>Description Type</label><select class="widefat"><option value="none">None</option><option value="bullet">Bullet Points</option><option value="number">Numbered Points</option></select></div>');
                $subTypeField.find('select').val(subtitle.type || 'none');
                $subTypeField.find('select').on('change', function() {
                    data.sections[sIndex].subtitles[subIndex].type = $(this).val();
                    if (!data.sections[sIndex].subtitles[subIndex].points) {
                        data.sections[sIndex].subtitles[subIndex].points = [''];
                    }
                    updateInput();
                    render(); // Re-render to show correct fields
                });
                
                $subBody.append($subTitleField).append($subTypeField);
                
                if (subtitle.type === 'none') {
                    const $descField = $('<div class="hz-field"><label>Description</label><textarea class="widefat" rows="4" placeholder="Normal description...">' + (subtitle.description||'') + '</textarea></div>');
                    $descField.find('textarea').on('input', function() {
                        data.sections[sIndex].subtitles[subIndex].description = $(this).val();
                        updateInput();
                    });
                    $subBody.append($descField);
                } else {
                    const $pointsContainer = $('<div class="hz-points"></div>');
                    if (!Array.isArray(subtitle.points)) subtitle.points = [''];
                    
                    subtitle.points.forEach((point, pIndex) => {
                        const $pointRow = $('<div class="hz-point-row"></div>');
                        const $pointInput = $('<input type="text" class="widefat" value="'+(point||'')+'" />');
                        $pointInput.on('input', function() {
                            data.sections[sIndex].subtitles[subIndex].points[pIndex] = $(this).val();
                            updateInput();
                        });
                        
                        const $pointDel = $('<button type="button" class="button button-small hz-point-del">x</button>');
                        $pointDel.on('click', function() {
                            data.sections[sIndex].subtitles[subIndex].points.splice(pIndex, 1);
                            updateInput();
                            render();
                        });
                        
                        $pointRow.append($pointInput).append($pointDel);
                        $pointsContainer.append($pointRow);
                    });
                    
                    const $addPointBtn = $('<button type="button" class="button hz-add-point">+ Add Point</button>');
                    $addPointBtn.on('click', function() {
                        data.sections[sIndex].subtitles[subIndex].points.push('');
                        updateInput();
                        render();
                    });
                    
                    $subBody.append('<div class="hz-field"><label>Points</label></div>').append($pointsContainer).append($addPointBtn);
                }
                
                $subToggleBtn.on('click', function() {
                    $subBody.slideToggle();
                    $(this).text($(this).text() === '+' ? '-' : '+');
                });
                
                $subDeleteBtn.on('click', function() {
                    if (confirm('Delete this Sub Title?')) {
                        data.sections[sIndex].subtitles.splice(subIndex, 1);
                        updateInput();
                        render();
                    }
                });
                
                $subWrapper.append($subHeader).append($subBody);
                $subtitlesContainer.append($subWrapper);
            });
            
            const $addSubBtn = $('<button type="button" class="button hz-add-subtitle">+ Add Sub Title</button>');
            $addSubBtn.on('click', function() {
                data.sections[sIndex].subtitles.push({ title: '', type: 'none', description: '', points: [''] });
                updateInput();
                render();
            });
            
            $sectionBody.append($subtitlesContainer).append($addSubBtn);
            
            $toggleBtn.on('click', function() {
                $sectionBody.slideToggle();
                $(this).text($(this).text() === '+' ? '-' : '+');
            });
            
            $deleteBtn.on('click', function() {
                if (confirm('Delete this Section?')) {
                    data.sections.splice(sIndex, 1);
                    updateInput();
                    render();
                }
            });
            
            $sectionWrapper.append($sectionHeader).append($sectionBody);
            $sectionsContainer.append($sectionWrapper);
        });

        $ui.append($sectionsContainer);
        
        const $addSectionBtn = $('<button type="button" class="button button-primary hz-add-section">+ Add New Section</button>');
        $addSectionBtn.on('click', function() {
            data.sections.push({ title: '', subtitles: [] });
            updateInput();
            render();
        });
        
        $ui.append($addSectionBtn);

        // Init Sortable
        if ($.fn.sortable) {
            $sectionsContainer.sortable({
                handle: '.hz-section-header h4',
                update: function() {
                    reorderSections();
                }
            });

            $('.hz-subtitles').sortable({
                handle: '.hz-subtitle-header h5',
                update: function() {
                    reorderSubtitles($(this));
                }
            });
            
            $('.hz-points').sortable({
                update: function() {
                    reorderPoints($(this));
                }
            });
        }
    }

    function reorderSections() {
        const newSections = [];
        $('.hz-section-wrapper').each(function() {
            const idx = $(this).data('index');
            if (data.sections[idx]) {
                newSections.push(data.sections[idx]);
            }
        });
        data.sections = newSections;
        updateInput();
        render(); // re-render to update indexes
    }

    function reorderSubtitles($container) {
        const sIndex = $container.closest('.hz-section-wrapper').data('index');
        const newSubs = [];
        $container.find('.hz-subtitle-wrapper').each(function() {
            const idx = $(this).data('index');
            if (data.sections[sIndex].subtitles[idx]) {
                newSubs.push(data.sections[sIndex].subtitles[idx]);
            }
        });
        data.sections[sIndex].subtitles = newSubs;
        updateInput();
        render();
    }
    
    function reorderPoints($container) {
        const sIndex = $container.closest('.hz-section-wrapper').data('index');
        const subIndex = $container.closest('.hz-subtitle-wrapper').data('index');
        const newPoints = [];
        $container.find('input').each(function() {
            newPoints.push($(this).val());
        });
        data.sections[sIndex].subtitles[subIndex].points = newPoints;
        updateInput();
        render();
    }

    function updateInput() {
        $input.val(JSON.stringify(data));
    }

    render();
});
