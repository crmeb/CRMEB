# encoding: utf-8
namespace :pear do

  support_files = ['README.md', 'LICENSE', 'protoc-gen-php.php', 'protoc-gen-php.bat']
  tpl_file = 'package.pear'
  xml_file = 'library/package.xml'

  desc "Generate package.xml"
  task :xml => [:clean] do |t, args|
    unless ENV['version'] then
      puts 'Version number not given. Use "pear:xml version=1.0"'
      exit 1
    end

    # Get template contents
    text = File.read(tpl_file, :encoding => "UTF-8")
    # Replace the version, date and time
    text = text.gsub("{VERSION}", ENV['version'])
    text = text.gsub('{DATE}', Time.now.strftime('%Y-%m-%d'))
    text = text.gsub('{TIME}', Time.now.strftime('%H:%M:%S'))

    # Include source files
    dirs = []
    Dir.glob('library/**/*.*') do |file|
      file[0, 'library/'.length] = ''
      dirs << '<file name="' + file + '" role="php">'
      dirs << '<tasks:replace from="@package_version@" to="version" type="package-info" />'
      dirs << '</file>'
    end

    text = text.gsub('{DIRS}', dirs.join("\n"))

    # Generate a new pear package.xml
    xml = File.new(xml_file, 'w')
    xml.syswrite(text);
    xml.close();
  end

  desc "Build a release"
  task :package => ['doc:build', :xml] do

    # Copy supporting files to the package root
    support_files.each do |file|
       cp file, "library/#{file}"
    end

    begin
      sh "pear package -n #{xml_file}"
    rescue Exception => e
      puts "Rolling back..."
      Rake::Task['pear:clean'].execute
      raise
    end

    Rake::Task['pear:clean'].execute
  end

  desc "Clean up"
  task :clean do
    puts "Cleaning up..."

    # Remove package.xml
    rm_f xml_file

    # Remove supporting files
    support_files.each { |file| rm_f "library/#{file}" }
  end

end


namespace :phar do

  phar_file = "protobuf.phar"

  desc "Build a phar archive"
  task :build do
    unless ENV['version'] then
      puts 'Version number not given. Use "phar:build version=1.0"'
      exit 1
    end

    puts "Creating a temporary directory with the files..."

    # Create a temporary directory
    tmp_dest = "/tmp/phar-#{Time.now.to_i}"
    mkdir_p tmp_dest

    # Copy library files there
    cp_r "library/", tmp_dest

    # Process copied files, replacing @package_version@
    version = ENV['version'] + '-phar'
    FileList["#{tmp_dest}/**/*.php"].each do |fpath|
      puts fpath
      sh "sed -i.bak 's/@package_version@/#{version}/g' #{fpath}" 
    end

    puts "Launching phar builder..."
    sh "phar pack -f #{phar_file} -s protoc-gen-php.php -c bz2 -h sha1 #{tmp_dest}/library"

    puts "Cleaning up temporary directory..."
    rm_r tmp_dest
  end

  desc "Clean up"
  task :clean do
    puts "Cleaning up..."

    # Remove the phar file
    rm_f phar_file
  end
end


namespace :doc do

  desc "Generate manual"
  task :build do
    version = ENV['version']
    ENV['RONN_MANUAL'] = "Protobuf-PHP #{version}"
    ENV['RONN_ORGANIZATION'] = "Ivan -DrSlump- Montes"
    sh "ronn -w -s toc -r5 --markdown man/*.ronn"
  end

  desc 'Publish to github pages'
  task :github => 'doc:build' do
    require 'git'
    require 'logger'

    remote = `git remote show origin`
             .split(%r{\n})  # Ruby 1.9 only has grep() on Array
             .grep(/Push.*URL/)
             .first[/git@.*/]

    files = [
      'protoc-gen-php.1.html',
      'protobuf-php.3.html',
      'protobuf-php.5.html',
    ]

    root = "/tmp/checkout-#{Time.now.to_i}"
    g = Git.clone(remote, root, :log => Logger.new(STDOUT))

    # Make sure this actually switches branches.
    g.checkout(g.branch('gh-pages'))
    
    files.each {|file|
      cp "man/#{file}", "#{root}/."
      g.add(file)
    }

    g.commit('Regenerating Github Pages.')

    # PUSH!
    g.push(g.remote('origin'), g.branch('gh-pages'))

    puts '--> GitHub Pages Commit and Push successful.'
  end

end


